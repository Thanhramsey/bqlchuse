<?php

namespace App\Models;

use CodeIgniter\Model;

class HouseholdModel extends Model
{
    protected $table            = 'households';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'household_code', 'owner_name', 'id_card', 'phone', 'address', 'ward_group', 'ward',
        'household_type', 'members_count', 'status', 'gps', 'route_id'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'household_code' => 'required|max_length[50]|is_unique[households.household_code,id,{id}]',
        'owner_name'     => 'required|max_length[255]',
        'address'        => 'required|max_length[255]',
        'ward_group'     => 'permit_empty|max_length[100]',
        'ward'           => 'permit_empty|max_length[100]',
        'household_type' => 'required',
        'members_count'  => 'permit_empty|integer',
        'status'         => 'required'
    ];
}
