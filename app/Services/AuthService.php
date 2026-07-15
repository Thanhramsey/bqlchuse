<?php

namespace App\Services;

use App\Models\UserModel;

class AuthService
{
    /**
     * Authenticate a user by username and password.
     *
     * @param string $username
     * @param string $password
     * @return array|string Returns user data array on success, or string error message on failure.
     */
    public function login(string $username, string $password)
    {
        $userModel = new UserModel();
        
        // Find user by username (active only)
        $user = $userModel->where('username', $username)->first();
        
        if (!$user) {
            return 'Tài khoản không tồn tại trên hệ thống.';
        }

        if ($user['status'] !== 'Hoạt động') {
            return 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên.';
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            return 'Mật khẩu không chính xác.';
        }

        // Update last login
        $userModel->update($user['id'], [
            'last_login' => date('Y-m-d H:i:s')
        ]);

        // Write session
        $session = session();
        $session->set([
            'is_logged_in' => true,
            'user_id'      => $user['id'],
            'username'     => $user['username'],
            'fullname'     => $user['fullname'],
            'role'         => $user['role'],
            'shift'        => $user['shift']
        ]);

        // Log action
        LogService::log('Login', 'Auth', 'Đăng nhập thành công', $user['id']);

        return $user;
    }

    /**
     * Log the current user out.
     */
    public function logout()
    {
        $session = session();
        if ($session->get('is_logged_in')) {
            LogService::log('Logout', 'Auth', 'Đăng xuất khỏi hệ thống');
        }
        $session->destroy();
    }
}
