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
        $db = \Config\Database::connect();
        
        $routes = $db->table('collection_routes')
            ->where('deleted_at', null)
            ->orderBy('route_name', 'ASC')
            ->get()
            ->getResultArray();

        $collectors = $db->table('users')
            ->where('status', 'Hoạt động')
            ->orderBy('fullname', 'ASC')
            ->get()
            ->getResultArray();

        return view('report/index', [
            'routes'     => $routes,
            'collectors' => $collectors
        ]);
    }

    /**
     * Fetch JSON summarized and detailed stats for reports display.
     */
    public function revenueData()
    {
        $fromMonth   = $this->request->getGet('from_month');
        $toMonth     = $this->request->getGet('to_month');
        $routeId     = $this->request->getGet('route_id') ? (int)$this->request->getGet('route_id') : null;
        $collectorId = $this->request->getGet('collector_id') ? (int)$this->request->getGet('collector_id') : null;

        $byRoute = $this->reportService->getRevenueByRoute($fromMonth, $toMonth);
        $byUser  = $this->reportService->getRevenueByCollector($fromMonth, $toMonth);
        $details = $this->reportService->getDetailedReport($fromMonth, $toMonth, $routeId, $collectorId);

        return $this->respond([
            'status' => true,
            'data'   => [
                'by_route'     => $byRoute,
                'by_collector' => $byUser,
                'details'      => $details
            ]
        ]);
    }

    /**
     * Export summarized and detailed revenue report to Excel format.
     */
    public function exportExcel()
    {
        $fromMonth   = $this->request->getGet('from_month');
        $toMonth     = $this->request->getGet('to_month');
        $routeId     = $this->request->getGet('route_id') ? (int)$this->request->getGet('route_id') : null;
        $collectorId = $this->request->getGet('collector_id') ? (int)$this->request->getGet('collector_id') : null;

        $byRoute = $this->reportService->getRevenueByRoute($fromMonth, $toMonth);
        $byUser  = $this->reportService->getRevenueByCollector($fromMonth, $toMonth);
        $details = $this->reportService->getDetailedReport($fromMonth, $toMonth, $routeId, $collectorId);

        $rangeText = "";
        if (!empty($fromMonth) || !empty($toMonth)) {
            $rangeText = " (từ " . ($fromMonth ?: 'đầu') . " đến " . ($toMonth ?: 'nay') . ")";
        }

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
        echo '<p>Khoảng thời gian thống kê: ' . ($rangeText ?: 'Tất cả') . '</p>';
        echo '<p>Ngày xuất báo cáo: ' . date('d/m/Y H:i:s') . '</p>';

        // Table 1: Route
        echo '<h3>1. Doanh thu theo tuyến thu gom</h3>';
        echo '<table border="1" cellpadding="5" cellspacing="0">';
        echo '<tr style="background:#f2f2f2;"><th>Mã tuyến</th><th>Tên tuyến</th><th>Số phiếu đã thu</th><th>Tổng tiền thu (VND)</th><th>Số HĐ đã xuất</th><th>Tổng tiền xuất HĐ (VND)</th></tr>';
        $totalCollectedAmt = 0;
        $totalCollectedCount = 0;
        $totalInvoicedAmt = 0;
        $totalInvoicedCount = 0;
        foreach ($byRoute as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['route_code'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['group_key'] ?? '') . '</td>';
            echo '<td>' . ($row['total_count'] ?? 0) . '</td>';
            echo '<td>' . number_format($row['total_amount'] ?? 0, 0, ',', '.') . '</td>';
            echo '<td>' . ($row['invoiced_count'] ?? 0) . '</td>';
            echo '<td>' . number_format($row['invoiced_amount'] ?? 0, 0, ',', '.') . '</td>';
            echo '</tr>';
            $totalCollectedAmt += ($row['total_amount'] ?? 0);
            $totalCollectedCount += ($row['total_count'] ?? 0);
            $totalInvoicedAmt += ($row['invoiced_amount'] ?? 0);
            $totalInvoicedCount += ($row['invoiced_count'] ?? 0);
        }
        // Total row
        echo '<tr style="font-weight:bold; background:#e6e6e6;">';
        echo '<td colspan="2">Tổng cộng</td>';
        echo '<td>' . $totalCollectedCount . '</td>';
        echo '<td>' . number_format($totalCollectedAmt, 0, ',', '.') . '</td>';
        echo '<td>' . $totalInvoicedCount . '</td>';
        echo '<td>' . number_format($totalInvoicedAmt, 0, ',', '.') . '</td>';
        echo '</tr>';
        echo '</table>';

        // Table 2: Collector
        echo '<h3>2. Doanh thu theo nhân viên thu ngân</h3>';
        echo '<table border="1" cellpadding="5" cellspacing="0">';
        echo '<tr style="background:#f2f2f2;"><th>Tài khoản</th><th>Họ tên nhân viên</th><th>Số phiếu đã thu</th><th>Tổng tiền thu (VND)</th><th>Số HĐ đã xuất</th><th>Tổng tiền xuất HĐ (VND)</th></tr>';
        $totalCollectedAmt2 = 0;
        $totalCollectedCount2 = 0;
        $totalInvoicedAmt2 = 0;
        $totalInvoicedCount2 = 0;
        foreach ($byUser as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['username'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['group_key'] ?? '') . '</td>';
            echo '<td>' . ($row['total_count'] ?? 0) . '</td>';
            echo '<td>' . number_format($row['total_amount'] ?? 0, 0, ',', '.') . '</td>';
            echo '<td>' . ($row['invoiced_count'] ?? 0) . '</td>';
            echo '<td>' . number_format($row['invoiced_amount'] ?? 0, 0, ',', '.') . '</td>';
            echo '</tr>';
            $totalCollectedAmt2 += ($row['total_amount'] ?? 0);
            $totalCollectedCount2 += ($row['total_count'] ?? 0);
            $totalInvoicedAmt2 += ($row['invoiced_amount'] ?? 0);
            $totalInvoicedCount2 += ($row['invoiced_count'] ?? 0);
        }
        // Total row
        echo '<tr style="font-weight:bold; background:#e6e6e6;">';
        echo '<td colspan="2">Tổng cộng</td>';
        echo '<td>' . $totalCollectedCount2 . '</td>';
        echo '<td>' . number_format($totalCollectedAmt2, 0, ',', '.') . '</td>';
        echo '<td>' . $totalInvoicedCount2 . '</td>';
        echo '<td>' . number_format($totalInvoicedAmt2, 0, ',', '.') . '</td>';
        echo '</tr>';
        echo '</table>';

        // Table 3: Detailed Report
        echo '<h3>3. Chi tiết các giao dịch thu phí</h3>';
        echo '<table border="1" cellpadding="5" cellspacing="0">';
        echo '<tr style="background:#f2f2f2;"><th>Mã hộ</th><th>Chủ hộ</th><th>Địa chỉ</th><th>Tuyến thu gom</th><th>Kỳ nộp</th><th>Số tiền (VND)</th><th>Trạng thái</th><th>Số phiếu thu</th><th>Số HĐ VNPT</th><th>Nhân viên thu</th><th>Ngày thu</th></tr>';
        $totalDetailedAmt = 0;
        foreach ($details as $row) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($row['household_code'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['owner_name'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['address'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['route_name'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['billing_month'] ?? '') . '</td>';
            echo '<td>' . number_format($row['amount'] ?? 0, 0, ',', '.') . '</td>';
            echo '<td>' . htmlspecialchars($row['payment_status'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['receipt_code'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['vnpt_inv_no'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['collector_name'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($row['payment_date'] ?? '') . '</td>';
            echo '</tr>';
            $totalDetailedAmt += ($row['amount'] ?? 0);
        }
        // Total row
        echo '<tr style="font-weight:bold; background:#e6e6e6;">';
        echo '<td colspan="5">Tổng cộng</td>';
        echo '<td>' . number_format($totalDetailedAmt, 0, ',', '.') . '</td>';
        echo '<td colspan="5"></td>';
        echo '</tr>';
        echo '</table>';

        echo '</body>';
        echo '</html>';
        exit;
    }

    /**
    * Render detailed report page with filter UI.
    */
    public function detailView()
    {
        $db = \Config\Database::connect();

        $routes = $db->table('collection_routes')
            ->where('deleted_at', null)
            ->orderBy('route_name', 'ASC')
            ->get()
            ->getResultArray();

        $collectors = $db->table('users')
            ->where('status', 'Hoạt động')
            ->orderBy('fullname', 'ASC')
            ->get()
            ->getResultArray();

        return view('report/detail_report/index', [
            'routes'     => $routes,
            'collectors' => $collectors,
        ]);
    }

    /**
    * Return JSON data for detailed report based on filters.
    */
    public function detailData()
    {
        $fromMonth   = $this->request->getGet('from_month');
        $toMonth     = $this->request->getGet('to_month');
        $routeId     = $this->request->getGet('route_id') ? (int)$this->request->getGet('route_id') : null;
        $collectorId = $this->request->getGet('collector_id') ? (int)$this->request->getGet('collector_id') : null;

        $details = $this->reportService->getDetailedReport($fromMonth, $toMonth, $routeId, $collectorId);
        return $this->respond(['status' => true, 'data' => $details]);
    }

    /**
    * Render PDF/Print preview format.
    */
    public function exportPdf()
    {
        $fromMonth   = $this->request->getGet('from_month');
        $toMonth     = $this->request->getGet('to_month');
        $routeId     = $this->request->getGet('route_id') ? (int)$this->request->getGet('route_id') : null;
        $collectorId = $this->request->getGet('collector_id') ? (int)$this->request->getGet('collector_id') : null;

        $db = \Config\Database::connect();

        $selectedRouteName = "";
        if (!empty($routeId)) {
            $r = $db->table('collection_routes')->where('id', $routeId)->get()->getRowArray();
            if ($r) $selectedRouteName = $r['route_name'];
        }

        $selectedCollectorName = "";
        if (!empty($collectorId)) {
            $u = $db->table('users')->where('id', $collectorId)->get()->getRowArray();
            if ($u) $selectedCollectorName = $u['fullname'];
        }

        $data = [
            'from_month'        => $fromMonth,
            'to_month'          => $toMonth,
            'selected_route'    => $selectedRouteName,
            'selected_collector'=> $selectedCollectorName,
            'by_route'          => $this->reportService->getRevenueByRoute($fromMonth, $toMonth),
            'by_collector'      => $this->reportService->getRevenueByCollector($fromMonth, $toMonth),
            'details'           => $this->reportService->getDetailedReport($fromMonth, $toMonth, $routeId, $collectorId)
        ];
        return view('report/print', $data);
    }
}
