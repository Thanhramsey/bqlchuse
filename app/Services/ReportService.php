<?php

namespace App\Services;

use App\Models\PaymentModel;

class ReportService
{
    protected PaymentModel $paymentModel;

    public function __construct()
    {
        $this->paymentModel = new PaymentModel();
    }

    /**
     * Get revenue aggregated by collection route (tuyến thu gom) with range filtering.
     */
    public function getRevenueByRoute(?string $fromMonth = null, ?string $toMonth = null): array
    {
        $builder = $this->paymentModel->select('
            cr.route_name as group_key,
            cr.route_code,
            COUNT(payments.id) as total_count,
            SUM(payments.amount) as total_amount,
            SUM(CASE WHEN payments.payment_status = "Đã xuất hóa đơn" THEN payments.amount ELSE 0 END) as invoiced_amount,
            COUNT(CASE WHEN payments.payment_status = "Đã xuất hóa đơn" THEN payments.id ELSE NULL END) as invoiced_count
        ')
        ->join('households h', 'h.id = payments.household_id')
        ->join('collection_routes cr', 'cr.id = h.route_id')
        ->whereIn('payments.payment_status', ['Đã thu tiền', 'Đã xuất hóa đơn']);

        if (!empty($fromMonth)) {
            $builder->where('payments.billing_from_month >=', $fromMonth);
        }
        if (!empty($toMonth)) {
            $builder->where('payments.billing_to_month <=', $toMonth);
        }

        return $builder->groupBy('cr.id, cr.route_name, cr.route_code')
            ->orderBy('total_amount', 'DESC')
            ->findAll();
    }

    /**
     * Get revenue aggregated by Collector (nhân viên thu ngân) with range filtering.
     */
    public function getRevenueByCollector(?string $fromMonth = null, ?string $toMonth = null): array
    {
        $builder = $this->paymentModel->select('
            u.fullname as group_key,
            u.username,
            COUNT(payments.id) as total_count,
            SUM(payments.amount) as total_amount,
            SUM(CASE WHEN payments.payment_status = "Đã xuất hóa đơn" THEN payments.amount ELSE 0 END) as invoiced_amount,
            COUNT(CASE WHEN payments.payment_status = "Đã xuất hóa đơn" THEN payments.id ELSE NULL END) as invoiced_count
        ')
        ->join('users u', 'u.id = payments.collected_by')
        ->whereIn('payments.payment_status', ['Đã thu tiền', 'Đã xuất hóa đơn']);

        if (!empty($fromMonth)) {
            $builder->where('payments.billing_from_month >=', $fromMonth);
        }
        if (!empty($toMonth)) {
            $builder->where('payments.billing_to_month <=', $toMonth);
        }

        return $builder->groupBy('u.id, u.fullname, u.username')
            ->orderBy('total_amount', 'DESC')
            ->findAll();
    }

    /**
     * Get detailed transaction report with range and entity filters.
     */
    public function getDetailedReport(?string $fromMonth = null, ?string $toMonth = null, ?int $routeId = null, ?int $collectorId = null): array
    {
        $builder = $this->paymentModel->select('
            payments.*,
            h.household_code,
            h.owner_name,
            h.address,
            cr.route_name,
            u.fullname as collector_name
        ')
        ->join('households h', 'h.id = payments.household_id')
        ->join('collection_routes cr', 'cr.id = h.route_id', 'left')
        ->join('users u', 'u.id = payments.collected_by', 'left')
        ->whereIn('payments.payment_status', ['Đã thu tiền', 'Đã xuất hóa đơn']);

        if (!empty($fromMonth)) {
            $builder->where('payments.billing_from_month >=', $fromMonth);
        }
        if (!empty($toMonth)) {
            $builder->where('payments.billing_to_month <=', $toMonth);
        }
        if (!empty($routeId)) {
            $builder->where('h.route_id', $routeId);
        }
        if (!empty($collectorId)) {
            $builder->where('payments.collected_by', $collectorId);
        }

        return $builder->orderBy('payments.payment_date', 'DESC')
            ->findAll();
    }
}
