<?php

namespace App\Services;

use App\Models\FeeRateModel;

class FeeRateService
{
    protected FeeRateModel $feeRateModel;

    public function __construct()
    {
        $this->feeRateModel = new FeeRateModel();
    }

    public function getFeeRatesList()
    {
        return $this->feeRateModel->orderBy('id', 'DESC')->findAll();
    }

    public function createFeeRate(array $data)
    {
        if (!$this->feeRateModel->insert($data)) {
            return $this->feeRateModel->errors();
        }
        LogService::log('Thêm', 'Mức phí', "Thêm mức phí mới cho {$data['household_type']}: " . format_money($data['price']));
        return true;
    }

    public function updateFeeRate(int $id, array $data)
    {
        if (!$this->feeRateModel->update($id, $data)) {
            return $this->feeRateModel->errors();
        }
        $rate = $this->feeRateModel->find($id);
        LogService::log('Sửa', 'Mức phí', "Sửa mức phí {$rate['household_type']}: " . format_money($rate['price']));
        return true;
    }

    public function deleteFeeRate(int $id)
    {
        $rate = $this->feeRateModel->find($id);
        if (!$rate) return false;

        if (!$this->feeRateModel->delete($id)) {
            return false;
        }
        LogService::log('Xóa', 'Mức phí', "Xóa cấu hình mức phí: {$rate['household_type']}");
        return true;
    }
}
