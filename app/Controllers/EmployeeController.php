<?php

namespace App\Controllers;

use App\Services\EmployeeService;
use CodeIgniter\API\ResponseTrait;

class EmployeeController extends BaseController
{
    use ResponseTrait;

    protected EmployeeService $employeeService;

    public function __construct()
    {
        $this->employeeService = new EmployeeService();
    }

    public function index()
    {
        return view('employee/index');
    }

    public function list()
    {
        $list = $this->employeeService->getEmployeesList();
        return $this->respond([
            'status' => true,
            'data'   => $list
        ]);
    }

    public function create()
    {
        $rules = [
            'username' => 'required|min_length[3]|max_length[100]|is_unique[users.username]',
            'password' => 'required|min_length[6]',
            'fullname' => 'required|max_length[255]',
            'role'     => 'required',
            'status'   => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Dữ liệu nhập vào không hợp lệ.',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'password' => $this->request->getPost('password'),
            'fullname' => $this->request->getPost('fullname'),
            'role'     => $this->request->getPost('role'),
            'shift'    => $this->request->getPost('shift') ?: null,
            'status'   => $this->request->getPost('status'),
        ];

        $result = $this->employeeService->createEmployee($data);

        if (is_array($result)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Lỗi lưu trữ.',
                'errors'  => $result
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Thêm nhân viên thành công.'
        ]);
    }

    public function update($id = null)
    {
        $id = (int)$id;
        $rules = [
            'username' => "required|min_length[3]|max_length[100]|is_unique[users.username,id,{$id}]",
            'fullname' => 'required|max_length[255]',
            'role'     => 'required',
            'status'   => 'required'
        ];

        // Only validate password if it is provided
        if ($this->request->getPost('password')) {
            $rules['password'] = 'min_length[6]';
        }

        if (!$this->validate($rules)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Dữ liệu nhập vào không hợp lệ.',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $data = [
            'username' => $this->request->getPost('username'),
            'fullname' => $this->request->getPost('fullname'),
            'role'     => $this->request->getPost('role'),
            'shift'    => $this->request->getPost('shift') ?: null,
            'status'   => $this->request->getPost('status'),
        ];

        if ($this->request->getPost('password')) {
            $data['password'] = $this->request->getPost('password');
        }

        $result = $this->employeeService->updateEmployee($id, $data);

        if (is_array($result)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Lỗi cập nhật.',
                'errors'  => $result
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Cập nhật nhân viên thành công.'
        ]);
    }

    public function delete($id = null)
    {
        $id = (int)$id;
        $result = $this->employeeService->deleteEmployee($id);

        if (!$result) {
            return $this->respond([
                'status'  => false,
                'message' => 'Nhân viên không tồn tại hoặc lỗi hệ thống.'
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Xóa nhân viên thành công.'
        ]);
    }
}
