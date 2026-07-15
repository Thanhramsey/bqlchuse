<?php

namespace App\Services;

use App\Models\SystemLogModel;

class LogService
{
    /**
     * Write an activity log to the database.
     *
     * @param string      $action      Login, Logout, Thêm, Sửa, Xóa, Thu phí, Import, Export, etc.
     * @param string      $module      Auth, Quản lý hộ dân, Tuyến thu gom, etc.
     * @param string      $description Detail description of the action.
     * @param int|null    $userId      The user who did this action (null defaults to session user).
     * @return bool
     */
    public static function log(string $action, string $module, string $description, ?int $userId = null): bool
    {
        $logModel = new SystemLogModel();
        $session  = session();
        
        // Default to logged-in user if userId is null
        if ($userId === null && $session->has('user_id')) {
            $userId = (int) $session->get('user_id');
        }

        // Capture request details
        $request = \Config\Services::request();
        $ip = $request->getIPAddress();

        $data = [
            'user_id'     => $userId ?: null,
            'action'      => $action,
            'module'      => $module,
            'description' => $description,
            'ip_address'  => $ip,
            'created_at'  => date('Y-m-d H:i:s'),
        ];

        return $logModel->insert($data) !== false;
    }
}
