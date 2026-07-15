<?php

namespace App\Controllers;

use App\Services\ReportService;
use CodeIgniter\API\ResponseTrait;

class ReportController extends BaseController
{
    use ResponseTrait;

    protected ReportService $reportService;

    public function __construct()
    {
        $this->reportService = new ReportService();
    }

    public function index()
    {
        return view('report/index');
    }

    /**
     * Fetch JSON summarized stats for reports display.
     */
    public function revenueData()
    {
        $byMonth = $this->reportService->getRevenueByMonth();
        $byType  = $this->reportService->getRevenueByHouseholdType();
        $byGroup = $this->reportService->getRevenueByWardGroup();
        $byUser  = $this->reportService->getRevenueByCollector();

        return $this->respond([
            'status' => true,
            'data'   => [
                'by_month'          => $byMonth,
                'by_household_type' => $byType,
                'by_ward_group'     => $byGroup,
                'by_collector'      => $byUser
            ]
        ]);
    }

    /**
     * Export summarized revenue report to Excel format.
     */
    public function exportExcel()
    {
        $byMonth = $this->reportService->getRevenueByMonth();
        $byType  = $this->reportService->getRevenueByHouseholdType();
        $byGroup = $this->reportService->getRevenueByWardGroup();
        $byUser  = $this->reportService->getRevenueByCollector();

        // Send HTTP headers to force Excel download
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="bao_cao_doanh_thu_moi_truong_' . date('Ymd') . '.xls"');
        header('Pragma: no-cache');
        header('Expires: 0');

        // Output simple XML/HTML structure Excel parses natively
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>';
        echo '<body>';
        
        echo '<h2>BÁO CÁO DOANH THU THU PHÍ RÁC THẢI - BAN QUẢN LÝ PHƯỜNG</h2>';
        echo '<p>Ngày xuất báo cáo: ' . date('d/m/Y H:i:s') . '</p>';

        // Table 1: Monthly
        echo '<h3>1. Doanh thu theo tháng</h3>';
        echo '<table border="1">';
        echo '<tr><th>Kỳ thu phí (Tháng)</th><th>Số hóa đơn đã nộp</th><th>Thành tiền (VND)</th></tr>';
        foreach ($byMonth as $row) {
            echo '<tr><td>' . $row['group_key'] . '</td><td>' . $row['bills_count'] . '</td><td>' . number_format($row['total_amount'], 0, ',', '.') . '</td></tr>';
        }
        echo '</table>';

        // Table 2: Household Type
        echo '<h3>2. Doanh thu theo phân loại hộ</h3>';
        echo '<table border="1">';
        echo '<tr><th>Loại hộ dân</th><th>Số hóa đơn đã nộp</th><th>Thành tiền (VND)</th></tr>';
        foreach ($byType as $row) {
            echo '<tr><td>' . $row['group_key'] . '</td><td>' . $row['bills_count'] . '</td><td>' . number_format($row['total_amount'], 0, ',', '.') . '</td></tr>';
        }
        echo '</table>';

        // Table 3: Ward Group
        echo '<h3>3. Doanh thu theo tổ dân phố</h3>';
        echo '<table border="1">';
        echo '<tr><th>Tổ dân phố</th><th>Số hóa đơn đã nộp</th><th>Thành tiền (VND)</th></tr>';
        foreach ($byGroup as $row) {
            echo '<tr><td>' . $row['group_key'] . '</td><td>' . $row['bills_count'] . '</td><td>' . number_format($row['total_amount'], 0, ',', '.') . '</td></tr>';
        }
        echo '</table>';

        // Table 4: Collector
        echo '<h3>4. Doanh thu theo nhân viên thu ngân</h3>';
        echo '<table border="1">';
        echo '<tr><th>Nhân viên thu ngân</th><th>Số hóa đơn đã nộp</th><th>Thành tiền (VND)</th></tr>';
        foreach ($byUser as $row) {
            echo '<tr><td>' . $row['group_key'] . '</td><td>' . $row['bills_count'] . '</td><td>' . number_format($row['total_amount'], 0, ',', '.') . '</td></tr>';
        }
        echo '</table>';

        echo '</body>';
        echo '</html>';
        exit;
    }

    /**
     * Render PDF/Print preview format.
     */
    public function exportPdf()
    {
        $data = [
            'by_month'          => $this->reportService->getRevenueByMonth(),
            'by_household_type' => $this->reportService->getRevenueByHouseholdType(),
            'by_ward_group'     => $this->reportService->getRevenueByWardGroup(),
            'by_collector'      => $this->reportService->getRevenueByCollector()
        ];
        return view('report/print', $data);
    }
}
