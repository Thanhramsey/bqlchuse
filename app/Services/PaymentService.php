<?php

namespace App\Services;

use App\Models\PaymentModel;
use App\Models\HouseholdModel;
use App\Models\FeeRateModel;

class PaymentService
{
    protected PaymentModel $paymentModel;

    public function __construct()
    {
        $this->paymentModel = new PaymentModel();
    }

    /**
     * Get active households with their latest range payment status and information.
     */
    public function getHouseholdsBillingList(?string $search = null, ?int $routeId = null, ?string $status = null, int $page = 1, int $perPage = 10): array
    {
        $db = \Config\Database::connect();
        
        // Subquery to get the latest paid/invoiced range payment for each household
        $subQuery = $db->table('payments')
            ->select('payments.*')
            ->join('(SELECT household_id, MAX(billing_to_month) as max_to_month FROM payments WHERE payment_status IN ("Đã thu tiền", "Đã xuất hóa đơn") GROUP BY household_id) p2', 
                   'payments.household_id = p2.household_id AND payments.billing_to_month = p2.max_to_month');

        $builder = $db->table('households h')
            ->select('h.id, h.household_code, h.owner_name, h.address, h.ward_group, cr.route_name, h.household_type,
                      p.billing_month as latest_month, p.payment_status as latest_status, p.payment_date as latest_date, p.receipt_code as latest_receipt, p.vnpt_inv_no as latest_vnpt_inv_no')
            ->join('collection_routes cr', 'cr.id = h.route_id', 'left')
            ->join('(' . $subQuery->getCompiledSelect() . ') p', 'h.id = p.household_id', 'left')
            ->where('h.deleted_at', null)
            ->where('h.status', 'Đang hoạt động');

        if (session()->get('role') === 'Nhân viên') {
            $uId = (int)session()->get('user_id');
            $assigned = $db->table('route_assignments')
                ->where('user_id', $uId)
                ->get()
                ->getResultArray();
            $directIds = array_column($assigned, 'route_id');
            if (empty($directIds)) {
                return ['list' => [], 'total' => 0];
            }
            $children = $db->table('collection_routes')
                ->select('id')
                ->whereIn('parent_id', $directIds)
                ->get()
                ->getResultArray();
            $childIds = array_column($children, 'id');
            $allowedRouteIds = array_unique(array_merge($directIds, $childIds));

            $builder->whereIn('h.route_id', $allowedRouteIds);
        }

        if (!empty($search)) {
            $builder->groupStart()
                ->like('h.owner_name', $search)
                ->orLike('h.household_code', $search)
                ->orLike('h.address', $search)
            ->groupEnd();
        }

        if (!empty($routeId)) {
            $builder->where('h.route_id', $routeId);
        }

        if (!empty($status)) {
            if ($status === 'Chưa thu tiền') {
                $builder->where('p.payment_status', null);
            } else {
                $builder->where('p.payment_status', $status);
            }
        }

        $total = $builder->countAllResults(false);
        $offset = ($page - 1) * $perPage;
        $list = $builder->orderBy('h.id', 'DESC')->limit($perPage, $offset)->get()->getResultArray();

        return [
            'list'  => $list,
            'total' => $total
        ];
    }

    /**
     * Get payment/invoice history list for a specific household.
     */
    public function getHouseholdHistory(int $householdId): array
    {
        return $this->paymentModel->select('payments.*, users.fullname as collector_name')
            ->join('users', 'users.id = payments.collected_by', 'left')
            ->where('payments.household_id', $householdId)
            ->whereIn('payments.payment_status', ['Đã thu tiền', 'Đã xuất hóa đơn'])
            ->orderBy('payments.billing_to_month', 'DESC')
            ->findAll();
    }

    /**
     * Process range payment for single or multiple households.
     * Actions:
     * - 'pay': sets status to 'Đã thu tiền'
     * - 'invoice': sets status to 'Đã xuất hóa đơn' (upgrades if already 'Đã thu tiền')
     */
    public function processRangePayment(array $householdIds, int $year, int $fromMonth, int $toMonth, string $action, ?int $userId = null): array
    {
        $db = \Config\Database::connect();
        $householdModel = new HouseholdModel();
        $feeRateModel = new FeeRateModel();

        // Load fee rates mapping
        $rates = $feeRateModel->where('status', 'Đang hiệu lực')->findAll();
        $rateMap = [];
        foreach ($rates as $r) {
            $rateMap[$r['household_type']] = $r;
        }

        $userId = $userId ?: session()->get('user_id');
        $targetStatus = ($action === 'invoice') ? 'Đã xuất hóa đơn' : 'Đã thu tiền';

        // Build bound strings
        $fromStr = $year . '-' . str_pad($fromMonth, 2, '0', STR_PAD_LEFT);
        $toStr = $year . '-' . str_pad($toMonth, 2, '0', STR_PAD_LEFT);
        $billingRangeDesc = $fromStr . ' đến ' . $toStr;

        $processedHouseholds = 0;
        $processedMonthsCount = 0;
        $skippedCount = 0;

        $db->transStart();

        foreach ($householdIds as $hId) {
            $h = $householdModel->find($hId);
            if (!$h) continue;

            // Get rate price & vat
            $price = 30000.00;
            $vat = 10.00;
            $rateId = null;
            if (isset($rateMap[$h['household_type']])) {
                $price = (float) $rateMap[$h['household_type']]['price'];
                $vat = (float) $rateMap[$h['household_type']]['vat'];
                $rateId = $rateMap[$h['household_type']]['id'];
            }

            $monthsCount = ($toMonth - $fromMonth) + 1;
            
            // Amount with tax: amount = months * price * (1 + vat/100)
            $subtotal = $price * $monthsCount;
            $taxAmount = $subtotal * ($vat / 100);
            $totalAmount = $subtotal + $taxAmount;

            // Check overlap
            $overlaps = $this->paymentModel->where('household_id', $hId)
                ->whereIn('payment_status', ['Đã thu tiền', 'Đã xuất hóa đơn'])
                ->groupStart()
                    ->where('billing_from_month <=', $toStr)
                    ->where('billing_to_month >=', $fromStr)
                ->groupEnd()
                ->findAll();

            if (!empty($overlaps)) {
                $upgraded = false;
                $overlapDetails = [];

                foreach ($overlaps as $ov) {
                    // Upgrade condition: action is invoice, existing is 'Đã thu tiền', bounds match exactly
                    if ($action === 'invoice' && $ov['payment_status'] === 'Đã thu tiền' 
                        && $ov['billing_from_month'] === $fromStr && $ov['billing_to_month'] === $toStr) {
                        
                        $receiptCode = $ov['receipt_code'] ?: 'PT-' . str_replace('-', '', $fromStr) . '-' . $h['household_code'] . '-' . strtoupper(substr(uniqid(), -4));
                        
                        $this->paymentModel->update($ov['id'], [
                            'payment_status' => 'Đã xuất hóa đơn',
                            'payment_date'   => date('Y-m-d H:i:s'),
                            'collected_by'   => $userId,
                            'receipt_code'   => $receiptCode,
                            'updated_at'     => date('Y-m-d H:i:s')
                        ]);
                        $upgraded = true;
                        $processedHouseholds++;
                        $processedMonthsCount += $monthsCount;
                    } else {
                        // Extract overlapping months
                        $ovStart = $ov['billing_from_month'];
                        $ovEnd = $ov['billing_to_month'];

                        $ovStartYear = (int)substr($ovStart, 0, 4);
                        $ovStartMonth = (int)substr($ovStart, 5, 2);
                        $ovEndYear = (int)substr($ovEnd, 0, 4);
                        $ovEndMonth = (int)substr($ovEnd, 5, 2);

                        $curY = $ovStartYear;
                        $curM = $ovStartMonth;
                        while (($curY < $ovEndYear) || ($curY == $ovEndYear && $curM <= $ovEndMonth)) {
                            $curStr = $curY . '-' . str_pad($curM, 2, '0', STR_PAD_LEFT);
                            if ($curStr >= $fromStr && $curStr <= $toStr) {
                                $overlapDetails[] = str_pad($curM, 2, '0', STR_PAD_LEFT) . '/' . $curY;
                            }
                            $curM++;
                            if ($curM > 12) {
                                $curM = 1;
                                $curY++;
                            }
                        }
                    }
                }

                if ($upgraded) {
                    continue;
                }

                if (!empty($overlapDetails)) {
                    $db->transRollback();
                    $monthsList = implode(', ', array_unique($overlapDetails));
                    return [
                        'status'  => false,
                        'message' => "Hộ dân {$h['owner_name']} ({$h['household_code']}) đã thanh toán cho các kỳ {$monthsList} rồi. Vui lòng kiểm tra lại khoảng thời gian thanh toán."
                    ];
                }

                $skippedCount++;
                continue;
            }

            // Create new single range payment record
            $receiptCode = 'PT-' . str_replace('-', '', $fromStr) . '-' . $h['household_code'] . '-' . strtoupper(substr(uniqid(), -4));
            
            $this->paymentModel->insert([
                'household_id'       => $hId,
                'billing_month'      => $billingRangeDesc,
                'billing_from_month' => $fromStr,
                'billing_to_month'   => $toStr,
                'amount'             => $totalAmount,
                'fee_rate_id'        => $rateId,
                'payment_status'     => $targetStatus,
                'payment_method'     => 'Tiền mặt',
                'payment_date'       => date('Y-m-d H:i:s'),
                'collected_by'       => $userId,
                'receipt_code'       => $receiptCode,
                'created_at'         => date('Y-m-d H:i:s'),
                'updated_at'         => date('Y-m-d H:i:s')
            ]);

            $processedHouseholds++;
            $processedMonthsCount += $monthsCount;
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return [
                'status'  => false,
                'message' => 'Lỗi xử lý cơ sở dữ liệu khi chạy thu phí.'
            ];
        }

        $logDesc = ($action === 'invoice') ? 'Xuất hóa đơn range' : 'Thu tiền range';
        LogService::log('Thu phí', 'Thu phí', "{$logDesc} cho {$processedHouseholds} hộ dân, tổng số tháng xử lý: {$processedMonthsCount}. Bỏ qua trùng lặp: {$skippedCount} hộ.");

        return [
            'status'  => true,
            'message' => 'Đã xử lý thành công cho ' . $processedHouseholds . ' hộ dân.' . ($skippedCount > 0 ? " (Bỏ qua {$skippedCount} hộ do trùng lặp kỳ thanh toán đã thu)." : ""),
            'count'   => $processedMonthsCount
        ];
    }
}
