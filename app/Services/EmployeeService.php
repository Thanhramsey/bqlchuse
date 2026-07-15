<?php

namespace App\Services;

use App\Models\UserModel;

class EmployeeService
{
    protected UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function getEmployeesList()
    {
        // Get all users except Super Admin
        return $this->userModel->where('role !=', 'Super Admin')
            ->orderBy('id', 'DESC')
            ->findAll();
    }

    public function createEmployee(array $data)
    {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        
        if (!$this->userModel->insert($data)) {
            return $this->userModel->errors();
        }
        LogService::log('Thêm', 'Nhân viên', "Thêm nhân viên mới: {$data['username']} ({$data['fullname']})");
        return true;
    }

    public function updateEmployee(int $id, array $data)
    {
        // Handle password update if provided
        if (!empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } else {
            unset($data['password']);
        }

        // Check if username is changed
        $user = $this->userModel->find($id);
        if (!$user) return false;

        if (!$this->userModel->update($id, $data)) {
            return $this->userModel->errors();
        }
        
        LogService::log('Sửa', 'Nhân viên', "Sửa nhân viên: {$user['username']} - {$user['fullname']}");
        return true;
    }

    public function deleteEmployee(int $id)
    {
        $user = $this->userModel->find($id);
        if (!$user) return false;

        if (!$this->userModel->delete($id)) {
            return false;
        }
        LogService::log('Xóa', 'Nhân viên', "Xóa nhân viên: {$user['username']} - {$user['fullname']}");
        return true;
    }
}
