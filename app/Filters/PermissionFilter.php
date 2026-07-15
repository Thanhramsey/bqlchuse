<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class PermissionFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        if (!$session->get('is_logged_in')) {
            return redirect()->to(base_url('login'))->with('error', 'Vui lòng đăng nhập để tiếp tục.');
        }

        $role = $session->get('role');
        
        // Super Admin has all permissions automatically
        if ($role === 'Super Admin') {
            return;
        }

        // Get required permission key from arguments
        $requiredPermission = $arguments[0] ?? null;
        if (!$requiredPermission) {
            return;
        }

        // Check permission in Database
        $db = \Config\Database::connect();
        $hasPermission = $db->table('role_permissions')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role', $role)
            ->where('permissions.permission_key', $requiredPermission)
            ->countAllResults() > 0;

        if (!$hasPermission) {
            // Check if AJAX request
            if ($request->isAJAX()) {
                $response = \Config\Services::response();
                $response->setStatusCode(430); // 403 Forbidden
                return $response->setJSON([
                    'status'  => false,
                    'message' => 'Bạn không có quyền thực hiện hành động này.'
                ]);
            }

            // Standard redirect
            return redirect()->to(base_url('dashboard'))->with('error', 'Bạn không có quyền truy cập chức năng này.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
