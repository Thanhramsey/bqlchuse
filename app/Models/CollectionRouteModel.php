<?php

namespace App\Models;

use CodeIgniter\Model;

class CollectionRouteModel extends Model
{
    protected $table            = 'collection_routes';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'route_code', 'route_name', 'parent_id', 'status'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [
        'route_code' => 'required|max_length[50]|is_unique[collection_routes.route_code,id,{id}]',
        'route_name' => 'required|max_length[255]',
        'status'     => 'required'
    ];
}
