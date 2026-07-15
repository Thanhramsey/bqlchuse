<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table            = 'payments';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'household_id', 'billing_month', 'billing_from_month', 'billing_to_month', 'amount', 'fee_rate_id', 'payment_status',
        'payment_method', 'payment_date', 'collected_by', 'receipt_code', 'qr_code_url',
        'vnpt_fkey', 'vnpt_inv_no', 'vnpt_issue_date'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [
        'household_id'       => 'required|integer',
        'billing_month'      => 'required|max_length[50]',
        'billing_from_month' => 'permit_empty|exact_length[7]',
        'billing_to_month'   => 'permit_empty|exact_length[7]',
        'amount'             => 'required|numeric',
        'payment_status'     => 'required'
    ];
}
