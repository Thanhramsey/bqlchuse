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
     * Get revenue aggregated by month.
     */
    public function getRevenueByMonth(): array
    {
        return $this->paymentModel->select('payments.billing_month as group_key, SUM(payments.amount) as total_amount, COUNT(payments.id) as bills_count')
            ->where('payments.payment_status', 'Đã thanh toán')
            ->groupBy('payments.billing_month')
            ->orderBy('payments.billing_month', 'DESC')
            ->findAll();
    }

    /**
     * Get revenue aggregated by household type.
     */
    public function getRevenueByHouseholdType(): array
    {
        return $this->paymentModel->select('households.household_type as group_key, SUM(payments.amount) as total_amount, COUNT(payments.id) as bills_count')
            ->join('households', 'households.id = payments.household_id')
            ->where('payments.payment_status', 'Đã thanh toán')
            ->groupBy('households.household_type')
            ->orderBy('total_amount', 'DESC')
            ->findAll();
    }

    /**
     * Get revenue aggregated by Ward Group (Tổ dân phố).
     */
    public function getRevenueByWardGroup(): array
    {
        return $this->paymentModel->select('households.ward_group as group_key, SUM(payments.amount) as total_amount, COUNT(payments.id) as bills_count')
            ->join('households', 'households.id = payments.household_id')
            ->where('payments.payment_status', 'Đã thanh toán')
            ->groupBy('households.ward_group')
            ->orderBy('total_amount', 'DESC')
            ->findAll();
    }

    /**
     * Get revenue aggregated by Collector (Nhân viên thu ngân).
     */
    public function getRevenueByCollector(): array
    {
        return $this->paymentModel->select('users.fullname as group_key, SUM(payments.amount) as total_amount, COUNT(payments.id) as bills_count')
            ->join('users', 'users.id = payments.collected_by')
            ->where('payments.payment_status', 'Đã thanh toán')
            ->groupBy('users.fullname')
            ->orderBy('total_amount', 'DESC')
            ->findAll();
    }
}
