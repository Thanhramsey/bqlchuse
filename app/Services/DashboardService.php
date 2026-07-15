<?php

namespace App\Services;

use App\Models\HouseholdModel;
use App\Models\PaymentModel;
use App\Models\UserModel;
use App\Models\SystemLogModel;

class DashboardService
{
    /**
     * Get summary KPI statistics.
     */
    public function getMetrics(): array
    {
        $hModel = new HouseholdModel();
        $pModel = new PaymentModel();
        $uModel = new UserModel();

        $totalHouseholds = $hModel->where('status', 'Đang hoạt động')->countAllResults();
        $totalEmployees  = $uModel->where('role !=', 'Super Admin')->countAllResults();

        // Total collected revenue
        $revenueRow = $pModel->selectSum('amount')
            ->where('payment_status', 'Đã thanh toán')
            ->first();
        $totalRevenue = isset($revenueRow['amount']) ? (float)$revenueRow['amount'] : 0.0;

        // Total pending / outstanding debt
        $debtRow = $pModel->selectSum('amount')
            ->whereIn('payment_status', ['Chưa thanh toán', 'Trễ hạn'])
            ->first();
        $totalPending = isset($debtRow['amount']) ? (float)$debtRow['amount'] : 0.0;

        return [
            'total_households' => $totalHouseholds,
            'total_employees'  => $totalEmployees,
            'total_revenue'    => $totalRevenue,
            'total_pending'    => $totalPending
        ];
    }

    /**
     * Get dynamic monthly revenue trends for the current year.
     */
    public function getMonthlyRevenueChartData(): array
    {
        $pModel = new PaymentModel();
        $year   = date('Y');

        $results = $pModel->select('billing_month, SUM(amount) as total')
            ->where('payment_status', 'Đã thanh toán')
            ->where("billing_month LIKE '{$year}-%'")
            ->groupBy('billing_month')
            ->orderBy('billing_month', 'ASC')
            ->findAll();

        $months = [];
        $totals = [];

        // Build data structure
        for ($m = 1; $m <= 12; $m++) {
            $monthKey = $year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
            $months[] = 'Tháng ' . $m;
            
            $foundVal = 0.0;
            foreach ($results as $row) {
                if ($row['billing_month'] === $monthKey) {
                    $foundVal = (float)$row['total'];
                    break;
                }
            }
            $totals[] = $foundVal;
        }

        return [
            'months' => $months,
            'totals' => $totals
        ];
    }

    /**
     * Get household composition stats (Hộ gia đình vs Hộ kinh doanh).
     */
    public function getHouseholdStats(): array
    {
        $hModel = new HouseholdModel();
        $results = $hModel->select('household_type, COUNT(id) as count')
            ->where('status', 'Đang hoạt động')
            ->groupBy('household_type')
            ->findAll();

        $labels = [];
        $counts = [];

        foreach ($results as $row) {
            $labels[] = $row['household_type'];
            $counts[] = (int)$row['count'];
        }

        return [
            'labels' => $labels,
            'counts' => $counts
        ];
    }

    /**
     * Get recent system activity logs.
     */
    public function getRecentLogs(): array
    {
        $logModel = new SystemLogModel();
        return $logModel->select('system_logs.*, users.fullname')
            ->join('users', 'users.id = system_logs.user_id', 'left')
            ->orderBy('system_logs.id', 'DESC')
            ->limit(5)
            ->findAll();
    }
}
