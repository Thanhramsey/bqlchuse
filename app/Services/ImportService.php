<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\CollectionRouteModel;
use App\Models\HouseholdModel;
use App\Models\FeeRateModel;

class ImportService
{
    /**
     * Import collection routes from an uploaded Excel or CSV file.
     *
     * Expected columns (row 1 = header):
     *   Mã tuyến | Tên tuyến | Mã tuyến cha | Trạng thái
     *
     * @return array ['success' => bool, 'imported' => int, 'errors' => []]
     */
    public function importRoutes(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (count($rows) < 2) {
            return ['success' => false, 'imported' => 0, 'errors' => ['File không có dữ liệu (chỉ có tiêu đề hoặc rỗng).']];
        }

        // Skip header row
        array_shift($rows);

        $routeModel = new CollectionRouteModel();
        $db = \Config\Database::connect();
        $errors = [];
        $importedCount = 0;

        // Build a map of route_code → id for existing routes (for parent_id lookup)
        $existingCodes = [];
        foreach ($routeModel->withDeleted()->findAll() as $r) {
            $existingCodes[$r['route_code']] = $r['id'];
        }

        // Pre-parse all rows to collect new route codes
        $pendingCodes = [];
        foreach ($rows as $rowIndex => $row) {
            $code = trim((string)($row[0] ?? ''));
            if ($code !== '') {
                $pendingCodes[$code] = $rowIndex + 2; // Excel row number
            }
        }

        $db->transStart();

        foreach ($rows as $rowIndex => $row) {
            $lineNum = $rowIndex + 2;

            $routeCode  = trim((string)($row[0] ?? ''));
            $routeName  = trim((string)($row[1] ?? ''));
            $parentCode = trim((string)($row[2] ?? ''));
            $status     = trim((string)($row[3] ?? '')) ?: 'Hoạt động';

            if ($routeCode === '' && $routeName === '') {
                continue; // skip blank rows
            }

            if ($routeCode === '') {
                $errors[] = "Dòng {$lineNum}: Thiếu Mã tuyến.";
                continue;
            }
            if ($routeName === '') {
                $errors[] = "Dòng {$lineNum}: Thiếu Tên tuyến.";
                continue;
            }

            // Check duplicate in DB
            if (isset($existingCodes[$routeCode])) {
                $errors[] = "Dòng {$lineNum}: Mã tuyến '{$routeCode}' đã tồn tại trong hệ thống, bỏ qua.";
                continue;
            }

            // Resolve parent_id
            $parentId = null;
            if ($parentCode !== '') {
                if (isset($existingCodes[$parentCode])) {
                    $parentId = $existingCodes[$parentCode];
                } elseif (isset($pendingCodes[$parentCode])) {
                    // Parent is in the same file but might not be inserted yet
                    // We'll do a lookup after insert loop - mark for retry
                    $parentId = null; // will fix in second pass if needed
                } else {
                    $errors[] = "Dòng {$lineNum}: Mã tuyến cha '{$parentCode}' không tồn tại.";
                    continue;
                }
            }

            $data = [
                'route_code' => $routeCode,
                'route_name' => $routeName,
                'parent_id'  => $parentId,
                'status'     => $status,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            $newId = $routeModel->insert($data);
            if ($newId) {
                $existingCodes[$routeCode] = $newId;
                $importedCount++;
            } else {
                $errs = $routeModel->errors();
                $errors[] = "Dòng {$lineNum}: Lỗi khi lưu - " . implode(', ', $errs);
            }
        }

        // Second pass: update parent_ids for rows where parent was in same file
        foreach ($rows as $rowIndex => $row) {
            $routeCode  = trim((string)($row[0] ?? ''));
            $parentCode = trim((string)($row[2] ?? ''));
            if ($routeCode === '' || $parentCode === '') continue;

            if (isset($existingCodes[$routeCode]) && isset($existingCodes[$parentCode])) {
                $routeModel->update($existingCodes[$routeCode], [
                    'parent_id'  => $existingCodes[$parentCode],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'imported' => 0, 'errors' => ['Lỗi giao dịch database, import bị hủy.']];
        }

        LogService::log('Import', 'Tuyến thu gom', "Đã import {$importedCount} tuyến từ Excel.");

        return [
            'success'  => true,
            'imported' => $importedCount,
            'errors'   => $errors,
        ];
    }

    /**
     * Import households from an uploaded Excel or CSV file.
     *
     * Expected columns (row 1 = header):
     *   Chủ hộ | CCCD | Số điện thoại | Địa chỉ | Loại hộ dân | Mã tuyến thu gom | Trạng thái | GPS
     *
     * @return array ['success' => bool, 'imported' => int, 'errors' => []]
     */
    public function importHouseholds(string $filePath): array
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, false);

        if (count($rows) < 2) {
            return ['success' => false, 'imported' => 0, 'errors' => ['File không có dữ liệu (chỉ có tiêu đề hoặc rỗng).']];
        }

        array_shift($rows);

        $householdModel = new HouseholdModel();
        $routeModel = new CollectionRouteModel();
        $feeRateModel = new FeeRateModel();
        $db = \Config\Database::connect();

        // Build route code → id map
        $routeCodeMap = [];
        foreach ($routeModel->findAll() as $r) {
            $routeCodeMap[$r['route_code']] = $r['id'];
        }

        // Build valid household types
        $validTypes = [];
        foreach ($feeRateModel->findAll() as $fr) {
            $validTypes[] = $fr['household_type'];
        }

        $errors = [];
        $importedCount = 0;

        // Get last household id for code generation
        $lastRow = $householdModel->orderBy('id', 'DESC')->first();
        $nextNum = $lastRow ? ($lastRow['id'] + 1) : 1;

        $db->transStart();

        foreach ($rows as $rowIndex => $row) {
            $lineNum    = $rowIndex + 2;
            $ownerName  = trim((string)($row[0] ?? ''));
            $idCard     = trim((string)($row[1] ?? ''));
            $phone      = trim((string)($row[2] ?? ''));
            $address    = trim((string)($row[3] ?? ''));
            $hType      = trim((string)($row[4] ?? ''));
            $routeCode  = trim((string)($row[5] ?? ''));
            $status     = trim((string)($row[6] ?? '')) ?: 'Đang hoạt động';
            $gps        = trim((string)($row[7] ?? ''));

            if ($ownerName === '' && $address === '') continue; // blank row

            if ($ownerName === '') {
                $errors[] = "Dòng {$lineNum}: Thiếu Tên chủ hộ.";
                continue;
            }
            if ($address === '') {
                $errors[] = "Dòng {$lineNum}: Thiếu Địa chỉ.";
                continue;
            }
            if ($hType === '') {
                $errors[] = "Dòng {$lineNum}: Thiếu Loại hộ dân.";
                continue;
            }
            if (!in_array($hType, $validTypes)) {
                $errors[] = "Dòng {$lineNum}: Loại hộ dân '{$hType}' không khớp với mức phí trong hệ thống. Các loại hợp lệ: " . implode(', ', $validTypes);
                continue;
            }

            // Resolve route
            $routeId = null;
            if ($routeCode !== '') {
                if (isset($routeCodeMap[$routeCode])) {
                    $routeId = $routeCodeMap[$routeCode];
                } else {
                    $errors[] = "Dòng {$lineNum}: Mã tuyến '{$routeCode}' không tồn tại trong hệ thống.";
                    continue;
                }
            }

            $householdCode = 'HD' . str_pad($nextNum, 5, '0', STR_PAD_LEFT);

            $data = [
                'household_code' => $householdCode,
                'owner_name'     => $ownerName,
                'id_card'        => $idCard ?: null,
                'phone'          => $phone ?: null,
                'address'        => $address,
                'ward_group'     => null,
                'ward'           => null,
                'household_type' => $hType,
                'members_count'  => 1,
                'status'         => $status,
                'gps'            => $gps ?: null,
                'route_id'       => $routeId,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ];

            if ($householdModel->insert($data)) {
                $importedCount++;
                $nextNum++;
            } else {
                $errs = $householdModel->errors();
                $errors[] = "Dòng {$lineNum}: Lỗi khi lưu - " . implode(', ', $errs);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return ['success' => false, 'imported' => 0, 'errors' => ['Lỗi giao dịch database, import bị hủy.']];
        }

        LogService::log('Import', 'Quản lý hộ dân', "Đã import {$importedCount} hộ dân từ Excel.");

        return [
            'success'  => true,
            'imported' => $importedCount,
            'errors'   => $errors,
        ];
    }

    /**
     * Generate a CSV template for route import.
     */
    public function generateRoutesTemplate(): string
    {
        $lines = [
            "\xEF\xBB\xBF", // BOM for Excel UTF-8 recognition
            "Mã tuyến,Tên tuyến,Mã tuyến cha (để trống nếu là tuyến gốc),Trạng thái\r\n",
            "T01,Tuyến Phường A,,Hoạt động\r\n",
            "T01.01,Tổ 1 - Phố Nguyễn Du,T01,Hoạt động\r\n",
        ];
        return implode('', $lines);
    }

    /**
     * Generate a CSV template for household import.
     */
    public function generateHouseholdsTemplate(): string
    {
        $lines = [
            "\xEF\xBB\xBF",
            "Tên chủ hộ,CCCD,Số điện thoại,Địa chỉ cụ thể,Loại hộ dân,Mã tuyến thu gom,Trạng thái,Tọa độ GPS\r\n",
            "Nguyễn Văn A,012345678901,0912345678,Số 10 Phố Nguyễn Du,Hộ gia đình,T01.01,Đang hoạt động,21.0228 105.8519\r\n",
            "Công ty TNHH ABC,,0243999888,Số 5 Đường Bà Triệu,Cơ quan/Xí nghiệp,T01.01,Đang hoạt động,\r\n",
        ];
        return implode('', $lines);
    }
}
