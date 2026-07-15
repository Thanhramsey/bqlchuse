<?php

namespace App\Controllers;

use App\Services\FeeRateService;
use CodeIgniter\API\ResponseTrait;

class FeeRateController extends BaseController
{
    use ResponseTrait;

    protected FeeRateService $feeRateService;

    public function __construct()
    {
        $this->feeRateService = new FeeRateService();
    }

    public function index()
    {
        return view('fee_rate/index');
    }

    public function list()
    {
        $list = $this->feeRateService->getFeeRatesList();
        return $this->respond([
            'status' => true,
            'data'   => $list
        ]);
    }

    public function create()
    {
        $rules = [
            'household_type' => 'required|max_length[100]',
            'price'          => 'required|numeric|greater_than_equal_to[0]',
            'vat'            => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
            'effective_date' => 'required|valid_date[Y-m-d]',
            'status'         => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $data = [
            'household_type' => $this->request->getPost('household_type'),
            'price'          => (float) $this->request->getPost('price'),
            'vat'            => (float) $this->request->getPost('vat'),
            'effective_date' => $this->request->getPost('effective_date'),
            'status'         => $this->request->getPost('status'),
        ];

        $result = $this->feeRateService->createFeeRate($data);

        if (is_array($result)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Lỗi lưu trữ.',
                'errors'  => $result
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Thêm mức phí thành công.'
        ]);
    }

    public function update($id = null)
    {
        $id = (int)$id;
        $rules = [
            'household_type' => 'required|max_length[100]',
            'price'          => 'required|numeric|greater_than_equal_to[0]',
            'vat'            => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
            'effective_date' => 'required|valid_date[Y-m-d]',
            'status'         => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Dữ liệu không hợp lệ.',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $data = [
            'household_type' => $this->request->getPost('household_type'),
            'price'          => (float) $this->request->getPost('price'),
            'vat'            => (float) $this->request->getPost('vat'),
            'effective_date' => $this->request->getPost('effective_date'),
            'status'         => $this->request->getPost('status'),
        ];

        $result = $this->feeRateService->updateFeeRate($id, $data);

        if (is_array($result)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Lỗi cập nhật.',
                'errors'  => $result
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Cập nhật mức phí thành công.'
        ]);
    }

    public function delete($id = null)
    {
        $id = (int)$id;
        $result = $this->feeRateService->deleteFeeRate($id);

        if (!$result) {
            return $this->respond([
                'status'  => false,
                'message' => 'Mức phí không tồn tại hoặc lỗi xử lý.'
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Xóa mức phí thành công.'
        ]);
    }
}
