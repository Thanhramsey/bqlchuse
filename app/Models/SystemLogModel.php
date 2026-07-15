<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemLogModel extends Model
{
    protected $table            = 'system_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'action', 'module', 'description', 'ip_address', 'created_at'];

    // Dates
    protected $useTimestamps = false;
}
