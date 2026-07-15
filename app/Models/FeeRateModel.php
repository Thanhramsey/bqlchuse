<?php

namespace App\Models;

use CodeIgniter\Model;

class FeeRateModel extends Model
{
    protected $table            = 'fee_rates';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = ['household_type', 'price', 'effective_date', 'status', 'vat'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'household_type' => 'required|max_length[100]',
        'price'          => 'required|numeric',
        'vat'            => 'required|numeric',
        'effective_date' => 'required|valid_date[Y-m-d]',
        'status'         => 'required'
    ];
}
