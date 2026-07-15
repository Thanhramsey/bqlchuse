<?php

namespace App\Controllers;

use App\Services\DashboardService;
use App\Models\SystemLogModel;
use CodeIgniter\API\ResponseTrait;

class DashboardController extends BaseController
{
    use ResponseTrait;

    protected DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = new DashboardService();
    }

    /**
     * Main dashboard view.
     */
    public function index()
    {
        if (session()->get('role') === 'Nhân viên') {
            return redirect()->to(base_url('routes'));
        }

        $data = [
            'metrics'    => $this->dashboardService->getMetrics(),
            'chartData'  => $this->dashboardService->getMonthlyRevenueChartData(),
            'stats'      => $this->dashboardService->getHouseholdStats(),
            'recentLogs' => $this->dashboardService->getRecentLogs()
        ];
        return view('dashboard', $data);
    }

    /**
     * View system log history page.
     */
    public function logs()
    {
        $logModel = new SystemLogModel();
        $logs = $logModel->select('system_logs.*, users.fullname')
            ->join('users', 'users.id = system_logs.user_id', 'left')
            ->orderBy('system_logs.id', 'DESC')
            ->findAll();

        return view('logs', ['logs' => $logs]);
    }

    /**
     * View configuration page.
     */
    public function config()
    {
        $configPath = WRITEPATH . 'system_config.json';
        $config = [];
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
        }

        $defaults = [
            'company_name'  => 'Ban Quản Lý Vệ Sinh Môi Trường Phường Nguyễn Du',
            'company_phone' => '024.3999888',
            'company_email' => 'bql.nguyendu@hanoi.gov.vn',
            'bank_id'       => 'vietinbank',
            'bank_account'  => '1133224455',
            'bank_name'     => 'BQL PHUONG NGUYEN DU',
            'PUBLISH_SERVICE_ADDRESS_ID'  => 'https://bvdkphutho-tt78admindemo.vnpt-invoice.com.vn/publishservice.asmx',
            'BUSINESS_SERVICE_ADDRESS_ID' => 'https://bvdkphutho-tt78admindemo.vnpt-invoice.com.vn/businessService.asmx',
            'PORTAL_SERVICE_ADDRESS_ID'   => 'https://bvdkphutho-tt78admindemo.vnpt-invoice.com.vn/portalservice.asmx',
            'WS_USER_ID'                  => 'wsmsservice',
            'WS_PASSWORD_ID'              => '123456aA@',
            'C_USER_ID'                   => 'bvdkphuthoadmin_demo',
            'C_PASSWORD_ID'               => '123456aA@78',
            'PATTERN_HD_ID'               => '1/003',
            'SERIAL_HD_ID'                => 'C23TAA'
        ];

        $config = array_merge($defaults, $config ?: []);
        
        // Write complete config back to avoid missing attributes
        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return view('config', ['config' => $config]);
    }

    /**
     * Update configurations.
     */
    public function updateConfig()
    {
        $rules = [
            'company_name'  => 'required',
            'company_phone' => 'required',
            'company_email' => 'required|valid_email',
            'bank_id'       => 'required',
            'bank_account'  => 'required',
            'bank_name'     => 'required',
            'PUBLISH_SERVICE_ADDRESS_ID'  => 'required',
            'BUSINESS_SERVICE_ADDRESS_ID' => 'required',
            'PORTAL_SERVICE_ADDRESS_ID'   => 'required',
            'WS_USER_ID'                  => 'required',
            'WS_PASSWORD_ID'              => 'required',
            'C_USER_ID'                   => 'required',
            'C_PASSWORD_ID'               => 'required',
            'PATTERN_HD_ID'               => 'required',
            'SERIAL_HD_ID'                => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Dữ liệu cấu hình không hợp lệ.',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $config = [
            'company_name'  => $this->request->getPost('company_name'),
            'company_phone' => $this->request->getPost('company_phone'),
            'company_email' => $this->request->getPost('company_email'),
            'bank_id'       => $this->request->getPost('bank_id'),
            'bank_account'  => $this->request->getPost('bank_account'),
            'bank_name'     => $this->request->getPost('bank_name'),
            'PUBLISH_SERVICE_ADDRESS_ID'  => $this->request->getPost('PUBLISH_SERVICE_ADDRESS_ID'),
            'BUSINESS_SERVICE_ADDRESS_ID' => $this->request->getPost('BUSINESS_SERVICE_ADDRESS_ID'),
            'PORTAL_SERVICE_ADDRESS_ID'   => $this->request->getPost('PORTAL_SERVICE_ADDRESS_ID'),
            'WS_USER_ID'                  => $this->request->getPost('WS_USER_ID'),
            'WS_PASSWORD_ID'              => $this->request->getPost('WS_PASSWORD_ID'),
            'C_USER_ID'                   => $this->request->getPost('C_USER_ID'),
            'C_PASSWORD_ID'               => $this->request->getPost('C_PASSWORD_ID'),
            'PATTERN_HD_ID'               => $this->request->getPost('PATTERN_HD_ID'),
            'SERIAL_HD_ID'                => $this->request->getPost('SERIAL_HD_ID')
        ];

        $configPath = WRITEPATH . 'system_config.json';
        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        \App\Services\LogService::log('Sửa', 'Cấu hình', 'Cập nhật cấu hình hệ thống');

        return $this->respond([
            'status'  => true,
            'message' => 'Cập nhật cấu hình hệ thống thành công.'
        ]);
    }
}
