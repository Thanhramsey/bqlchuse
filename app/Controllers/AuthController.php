<?php

namespace App\Controllers;

use App\Services\AuthService;
use CodeIgniter\API\ResponseTrait;

class AuthController extends BaseController
{
    use ResponseTrait;

    protected AuthService $authService;

    public function __construct()
    {
        $this->authService = new AuthService();
    }

    /**
     * Show the login screen.
     */
    public function login()
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to(base_url('dashboard'));
        }
        return view('auth/login');
    }

    /**
     * Process login form submission via AJAX.
     */
    public function attemptLogin()
    {
        // Validation rules
        $rules = [
            'username' => [
                'label' => 'Tài khoản',
                'rules' => 'required|min_length[3]|max_length[100]',
                'errors' => [
                    'required' => 'Vui lòng nhập tên tài khoản.',
                    'min_length' => 'Tên tài khoản phải từ {param} ký tự trở lên.',
                    'max_length' => 'Tên tài khoản không vượt quá {param} ký tự.'
                ]
            ],
            'password' => [
                'label' => 'Mật khẩu',
                'rules' => 'required|min_length[6]',
                'errors' => [
                    'required' => 'Vui lòng nhập mật khẩu.',
                    'min_length' => 'Mật khẩu phải chứa ít nhất {param} ký tự.'
                ]
            ]
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Dữ liệu đầu vào không hợp lệ.',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $username = $this->request->getPost('username');
        $password = $this->request->getPost('password');

        $result = $this->authService->login($username, $password);

        if (is_string($result)) {
            // Error message returned
            return $this->respond([
                'status'  => false,
                'message' => $result,
                'errors'  => []
            ]);
        }

        // Successfully logged in
        return $this->respond([
            'status'  => true,
            'message' => 'Đăng nhập thành công! Đang chuyển hướng...',
            'data'    => [
                'redirect' => base_url('dashboard')
            ]
        ]);
    }

    /**
     * Log out of the system.
     */
    public function logout()
    {
        $this->authService->logout();
        return redirect()->to(base_url('login'))->with('success', 'Đăng xuất thành công.');
    }
}
