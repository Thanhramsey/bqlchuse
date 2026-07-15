<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // 1. Seed Permissions & Role Permissions
        $permissions = [
            // Dashboard
            ['module_name' => 'Dashboard', 'permission_key' => 'dashboard.view', 'description' => 'Xem bảng điều khiển'],
            
            // Households
            ['module_name' => 'Quản lý hộ dân', 'permission_key' => 'households.view', 'description' => 'Xem danh sách hộ dân'],
            ['module_name' => 'Quản lý hộ dân', 'permission_key' => 'households.create', 'description' => 'Thêm mới hộ dân'],
            ['module_name' => 'Quản lý hộ dân', 'permission_key' => 'households.edit', 'description' => 'Sửa thông tin hộ dân'],
            ['module_name' => 'Quản lý hộ dân', 'permission_key' => 'households.delete', 'description' => 'Xóa hộ dân'],
            
            // Routes
            ['module_name' => 'Tuyến thu gom', 'permission_key' => 'routes.view', 'description' => 'Xem danh sách tuyến thu gom'],
            ['module_name' => 'Tuyến thu gom', 'permission_key' => 'routes.create', 'description' => 'Thêm mới tuyến thu gom'],
            ['module_name' => 'Tuyến thu gom', 'permission_key' => 'routes.edit', 'description' => 'Sửa tuyến thu gom'],
            ['module_name' => 'Tuyến thu gom', 'permission_key' => 'routes.delete', 'description' => 'Xóa tuyến thu gom'],
            
            // Employees
            ['module_name' => 'Nhân viên', 'permission_key' => 'employees.view', 'description' => 'Xem danh sách nhân viên'],
            ['module_name' => 'Nhân viên', 'permission_key' => 'employees.create', 'description' => 'Thêm mới nhân viên'],
            ['module_name' => 'Nhân viên', 'permission_key' => 'employees.edit', 'description' => 'Sửa nhân viên'],
            ['module_name' => 'Nhân viên', 'permission_key' => 'employees.delete', 'description' => 'Xóa nhân viên'],
            
            // Fee rates
            ['module_name' => 'Mức phí', 'permission_key' => 'fee_rates.view', 'description' => 'Xem cấu hình mức phí'],
            ['module_name' => 'Mức phí', 'permission_key' => 'fee_rates.create', 'description' => 'Thêm mới mức phí'],
            ['module_name' => 'Mức phí', 'permission_key' => 'fee_rates.edit', 'description' => 'Sửa mức phí'],
            ['module_name' => 'Mức phí', 'permission_key' => 'fee_rates.delete', 'description' => 'Xóa mức phí'],
            
            // Payments
            ['module_name' => 'Thu phí', 'permission_key' => 'payments.view', 'description' => 'Xem danh sách thanh toán'],
            ['module_name' => 'Thu phí', 'permission_key' => 'payments.create', 'description' => 'Lập phiếu/Thu tiền phí'],
            ['module_name' => 'Thu phí', 'permission_key' => 'payments.edit', 'description' => 'Hủy/Sửa phiếu thu phí'],
            
            // Debts
            ['module_name' => 'Công nợ', 'permission_key' => 'debts.view', 'description' => 'Xem danh sách nợ'],
            ['module_name' => 'Công nợ', 'permission_key' => 'debts.remind', 'description' => 'Gửi nhắc nhở thu nợ'],
            
            // Reports
            ['module_name' => 'Báo cáo', 'permission_key' => 'reports.view', 'description' => 'Xem báo cáo doanh thu'],
            ['module_name' => 'Báo cáo', 'permission_key' => 'reports.export', 'description' => 'Xuất Excel/PDF báo cáo'],
            
            // Config
            ['module_name' => 'Cấu hình', 'permission_key' => 'config.view', 'description' => 'Xem cấu hình hệ thống'],
            ['module_name' => 'Cấu hình', 'permission_key' => 'config.edit', 'description' => 'Sửa cấu hình hệ thống']
        ];

        // Insert Permissions
        $db = \Config\Database::connect();
        $db->table('permissions')->insertBatch($permissions);

        // Fetch permissions to map IDs
        $permIds = [];
        foreach ($db->table('permissions')->get()->getResultArray() as $p) {
            $permIds[$p['permission_key']] = $p['id'];
        }

        // Map roles and permissions keys
        $roleMapping = [
            'Super Admin' => array_keys($permIds),
            'Admin' => array_keys($permIds),
            'Nhân viên' => ['dashboard.view', 'routes.view', 'reports.view'],
            'Thu ngân' => ['dashboard.view', 'households.view', 'payments.view', 'payments.create', 'payments.edit', 'debts.view', 'debts.remind'],
            'Kế toán' => ['dashboard.view', 'payments.view', 'debts.view', 'reports.view', 'reports.export'],
            'Lãnh đạo' => ['dashboard.view', 'households.view', 'routes.view', 'employees.view', 'fee_rates.view', 'payments.view', 'debts.view', 'reports.view', 'reports.export', 'config.view']
        ];

        $rolePermsData = [];
        foreach ($roleMapping as $role => $keys) {
            foreach ($keys as $key) {
                if (isset($permIds[$key])) {
                    $rolePermsData[] = [
                        'role' => $role,
                        'permission_id' => $permIds[$key]
                    ];
                }
            }
        }
        $db->table('role_permissions')->insertBatch($rolePermsData);

        // 2. Seed Users
        $users = [
            [
                'username'   => 'superadmin',
                'password'   => password_hash('superadmin123', PASSWORD_DEFAULT),
                'fullname'   => 'Quản trị viên Cấp cao',
                'role'       => 'Super Admin',
                'shift'      => null,
                'status'     => 'Hoạt động',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username'   => 'admin',
                'password'   => password_hash('admin123', PASSWORD_DEFAULT),
                'fullname'   => 'Quản trị viên Phường',
                'role'       => 'Admin',
                'shift'      => null,
                'status'     => 'Hoạt động',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username'   => 'nv_hung',
                'password'   => password_hash('hung123', PASSWORD_DEFAULT),
                'fullname'   => 'Nguyễn Văn Hùng (Nhân viên thu)',
                'role'       => 'Nhân viên',
                'shift'      => 'Ca sáng',
                'status'     => 'Hoạt động',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username'   => 'nv_lan',
                'password'   => password_hash('lan123', PASSWORD_DEFAULT),
                'fullname'   => 'Trần Thị Lan (Thu ngân)',
                'role'       => 'Thu ngân',
                'shift'      => 'Ca chiều',
                'status'     => 'Hoạt động',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username'   => 'nv_khoa',
                'password'   => password_hash('khoa123', PASSWORD_DEFAULT),
                'fullname'   => 'Phạm Minh Khoa (Kế toán)',
                'role'       => 'Kế toán',
                'shift'      => null,
                'status'     => 'Hoạt động',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'username'   => 'ld_nam',
                'password'   => password_hash('nam123', PASSWORD_DEFAULT),
                'fullname'   => 'Vũ Hoài Nam (Lãnh đạo)',
                'role'       => 'Lãnh đạo',
                'shift'      => null,
                'status'     => 'Hoạt động',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        $db->table('users')->insertBatch($users);

        // Fetch seeded user IDs
        $userIds = [];
        foreach ($db->table('users')->get()->getResultArray() as $u) {
            $userIds[$u['username']] = $u['id'];
        }

        // 3. Seed Fee Rates
        $feeRates = [
            [
                'household_type' => 'Hộ gia đình',
                'price'          => 30000.00,
                'vat'            => 10.00,
                'effective_date' => '2026-01-01',
                'status'         => 'Đang hiệu lực',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s')
            ],
            [
                'household_type' => 'Hộ kinh doanh',
                'price'          => 150000.00,
                'vat'            => 10.00,
                'effective_date' => '2026-01-01',
                'status'         => 'Đang hiệu lực',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s')
            ],
            [
                'household_type' => 'Cơ quan/Xí nghiệp',
                'price'          => 300000.00,
                'vat'            => 10.00,
                'effective_date' => '2026-01-01',
                'status'         => 'Đang hiệu lực',
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s')
            ]
        ];
        $db->table('fee_rates')->insertBatch($feeRates);

        // 4. Seed Hierarchical Collection Routes
        // 4a. Parent Routes (Tuyến cha)
        $parentRoutes = [
            [
                'route_code' => 'T01',
                'route_name' => 'Tuyến Phường Nguyễn Du',
                'parent_id'  => null,
                'status'     => 'Hoạt động',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'route_code' => 'T02',
                'route_name' => 'Tuyến Phường Bùi Thị Xuân',
                'parent_id'  => null,
                'status'     => 'Hoạt động',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        $db->table('collection_routes')->insertBatch($parentRoutes);

        // Fetch parent IDs
        $pIds = [];
        foreach ($db->table('collection_routes')->get()->getResultArray() as $pr) {
            $pIds[$pr['route_code']] = $pr['id'];
        }

        // 4b. Child Routes (Tuyến con)
        $childRoutes = [
            [
                'route_code' => 'T01.01',
                'route_name' => 'Khu phố Nguyễn Du - Tổ 1',
                'parent_id'  => $pIds['T01'],
                'status'     => 'Hoạt động',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'route_code' => 'T01.02',
                'route_name' => 'Khu phố Nguyễn Du - Tổ 2',
                'parent_id'  => $pIds['T01'],
                'status'     => 'Hoạt động',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'route_code' => 'T02.01',
                'route_name' => 'Khu phố Bà Triệu - Tổ 1',
                'parent_id'  => $pIds['T02'],
                'status'     => 'Hoạt động',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ];
        $db->table('collection_routes')->insertBatch($childRoutes);

        // Fetch child IDs
        $cIds = [];
        foreach ($db->table('collection_routes')->where('parent_id !=', null)->get()->getResultArray() as $cr) {
            $cIds[$cr['route_code']] = $cr['id'];
        }

        // 4c. Route Assignments (Phân tuyến cho nhân viên thu)
        $assignments = [
            [
                'route_id' => $cIds['T01.01'],
                'user_id'  => $userIds['nv_lan'] // Lan (Thu ngân) phụ trách Tổ 1 Nguyễn Du
            ],
            [
                'route_id' => $cIds['T01.02'],
                'user_id'  => $userIds['nv_lan'] // Lan phụ trách Tổ 2 Nguyễn Du
            ],
            [
                'route_id' => $cIds['T01.01'],
                'user_id'  => $userIds['nv_hung'] // Hùng (Nhân viên) cũng tham gia hỗ trợ Tổ 1
            ],
            [
                'route_id' => $cIds['T02.01'],
                'user_id'  => $userIds['nv_hung'] // Hùng phụ trách chính Tổ 1 Bà Triệu
            ]
        ];
        $db->table('route_assignments')->insertBatch($assignments);

        // 5. Seed Households
        $households = [
            [
                'household_code' => 'HD00001',
                'owner_name'     => 'Nguyễn Văn An',
                'id_card'         => '001095012345',
                'phone'          => '0912345678',
                'address'        => 'Số 10, Phố Nguyễn Du',
                'ward_group'     => 'Tổ dân phố 1',
                'ward'           => 'Phường Nguyễn Du',
                'household_type' => 'Hộ gia đình',
                'members_count'  => 4,
                'status'         => 'Đang hoạt động',
                'gps'            => '21.0228, 105.8519',
                'route_id'       => $cIds['T01.01'],
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s')
            ],
            [
                'household_code' => 'HD00002',
                'owner_name'     => 'Trần Văn Bình',
                'id_card'         => '001095067890',
                'phone'          => '0987654321',
                'address'        => 'Số 15, Phố Nguyễn Du',
                'ward_group'     => 'Tổ dân phố 1',
                'ward'           => 'Phường Nguyễn Du',
                'household_type' => 'Hộ kinh doanh',
                'members_count'  => 5,
                'status'         => 'Đang hoạt động',
                'gps'            => '21.0229, 105.8521',
                'route_id'       => $cIds['T01.01'],
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s')
            ],
            [
                'household_code' => 'HD00003',
                'owner_name'     => 'Công ty TNHH Nhựa Song Long',
                'id_card'         => null,
                'phone'          => '0243999888',
                'address'        => 'Số 100, Phố Bà Triệu',
                'ward_group'     => 'Tổ dân phố 2',
                'ward'           => 'Phường Nguyễn Du',
                'household_type' => 'Cơ quan/Xí nghiệp',
                'members_count'  => 25,
                'status'         => 'Đang hoạt động',
                'gps'            => '21.0215, 105.8510',
                'route_id'       => $cIds['T02.01'],
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s')
            ],
            [
                'household_code' => 'HD00004',
                'owner_name'     => 'Lê Thị Cúc',
                'id_card'         => '001095000111',
                'phone'          => '0945678912',
                'address'        => 'Số 32, Phố Bà Triệu',
                'ward_group'     => 'Tổ dân phố 2',
                'ward'           => 'Phường Nguyễn Du',
                'household_type' => 'Hộ gia đình',
                'members_count'  => 2,
                'status'         => 'Tạm ngưng',
                'gps'            => '21.0218, 105.8512',
                'route_id'       => $cIds['T02.01'],
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s')
            ]
        ];
        $db->table('households')->insertBatch($households);

        // Fetch household IDs
        $householdIds = [];
        foreach ($db->table('households')->get()->getResultArray() as $h) {
            $householdIds[$h['household_code']] = $h;
        }

        // Fetch fee rate IDs
        $rateIds = [];
        foreach ($db->table('fee_rates')->get()->getResultArray() as $fr) {
            $rateIds[$fr['household_type']] = $fr;
        }

        // 6. Seed Payments (Thu phí)
        $payments = [
            [
                'household_id'   => $householdIds['HD00001']['id'],
                'billing_month'  => '2026-05 đến 2026-05',
                'billing_from_month' => '2026-05',
                'billing_to_month'   => '2026-05',
                'amount'         => 33000.00, // 30000 + 10% VAT
                'fee_rate_id'    => $rateIds['Hộ gia đình']['id'],
                'payment_status' => 'Đã xuất hóa đơn',
                'payment_method' => 'Tiền mặt',
                'payment_date'   => '2026-05-10 09:30:00',
                'collected_by'   => $userIds['nv_lan'],
                'receipt_code'   => 'BL-202605-HD00001-A1B2',
                'qr_code_url'    => null,
                'created_at'     => '2026-05-01 00:00:00',
                'updated_at'     => '2026-05-10 09:30:00'
            ],
            [
                'household_id'   => $householdIds['HD00002']['id'],
                'billing_month'  => '2026-05 đến 2026-05',
                'billing_from_month' => '2026-05',
                'billing_to_month'   => '2026-05',
                'amount'         => 165000.00, // 150000 + 10% VAT
                'fee_rate_id'    => $rateIds['Hộ kinh doanh']['id'],
                'payment_status' => 'Đã xuất hóa đơn',
                'payment_method' => 'Tiền mặt',
                'payment_date'   => '2026-05-12 15:45:00',
                'collected_by'   => $userIds['nv_lan'],
                'receipt_code'   => 'BL-202605-HD00002-C3D4',
                'qr_code_url'    => null,
                'created_at'     => '2026-05-01 00:00:00',
                'updated_at'     => '2026-05-12 15:45:00'
            ],
            [
                'household_id'   => $householdIds['HD00002']['id'],
                'billing_month'  => '2026-06 đến 2026-06',
                'billing_from_month' => '2026-06',
                'billing_to_month'   => '2026-06',
                'amount'         => 165000.00, // 150000 + 10% VAT
                'fee_rate_id'    => $rateIds['Hộ kinh doanh']['id'],
                'payment_status' => 'Đã thu tiền',
                'payment_method' => 'Tiền mặt',
                'payment_date'   => '2026-06-11 10:20:00',
                'collected_by'   => $userIds['nv_lan'],
                'receipt_code'   => 'BL-202606-HD00002-E5F6',
                'qr_code_url'    => null,
                'created_at'     => '2026-06-01 00:00:00',
                'updated_at'     => '2026-06-11 10:20:00'
            ]
        ];
        $db->table('payments')->insertBatch($payments);

        // 7. Seed Initial Logs
        $logs = [
            [
                'user_id'     => $userIds['superadmin'],
                'action'      => 'Login',
                'module'      => 'Auth',
                'description' => 'Đăng nhập hệ thống lần đầu tiên',
                'ip_address'  => '127.0.0.1',
                'created_at'  => date('Y-m-d H:i:s', strtotime('-1 hour'))
            ],
            [
                'user_id'     => $userIds['superadmin'],
                'action'      => 'Import',
                'module'      => 'Quản lý hộ dân',
                'description' => 'Khởi tạo dữ liệu mẫu cho hệ thống (Hierarchical Routing)',
                'ip_address'  => '127.0.0.1',
                'created_at'  => date('Y-m-d H:i:s', strtotime('-50 minutes'))
            ]
        ];
        $db->table('system_logs')->insertBatch($logs);
    }
}
