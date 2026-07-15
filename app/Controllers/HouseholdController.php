<?php

namespace App\Controllers;

use App\Services\HouseholdService;
use App\Services\ImportService;
use CodeIgniter\API\ResponseTrait;

class HouseholdController extends BaseController
{
    use ResponseTrait;

    protected HouseholdService $householdService;
    protected ImportService $importService;

    public function __construct()
    {
        $this->householdService = new HouseholdService();
        $this->importService = new ImportService();
    }

    /**
     * Show household listing page.
     */
    public function index()
    {
        $data = [
            'routes'   => $this->householdService->getRoutesList(),
            'feeRates' => $this->householdService->getFeeRatesList(),
        ];
        return view('household/index', $data);
    }

    /**
     * Fetch JSON list of households for datatable.
     */
    public function list()
    {
        $search      = $this->request->getGet('search') ?: null;
        $routeId     = $this->request->getGet('route_id') ? (int)$this->request->getGet('route_id') : null;
        $page        = $this->request->getGet('page') ? (int)$this->request->getGet('page') : 1;
        $perPage     = $this->request->getGet('per_page') ? (int)$this->request->getGet('per_page') : 10;
        $showDeleted = $this->request->getGet('show_deleted') == '1';

        $result = $this->householdService->getHouseholdsList($search, $routeId, $page, $perPage, $showDeleted);
        return $this->respond([
            'status'      => true,
            'data'        => $result['list'],
            'total'       => $result['total'],
            'page'        => $page,
            'per_page'    => $perPage,
            'total_pages' => ceil($result['total'] / $perPage)
        ]);
    }

    /**
     * Create a new household.
     */
    public function create()
    {
        $rules = [
            'owner_name'     => 'required|min_length[3]|max_length[255]',
            'address'        => 'required|max_length[255]',
            'household_type' => 'required',
            'status'         => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Vui lòng kiểm tra lại thông tin.',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $data = [
            'owner_name'     => $this->request->getPost('owner_name'),
            'id_card'        => $this->request->getPost('id_card') ?: null,
            'phone'          => $this->request->getPost('phone') ?: null,
            'address'        => $this->request->getPost('address'),
            'ward_group'     => $this->request->getPost('ward_group'),
            'ward'           => $this->request->getPost('ward'),
            'household_type' => $this->request->getPost('household_type'),
            'members_count'  => (int) $this->request->getPost('members_count'),
            'status'         => $this->request->getPost('status'),
            'gps'            => $this->request->getPost('gps') ?: null,
            'route_id'       => $this->request->getPost('route_id') ? (int) $this->request->getPost('route_id') : null,
        ];

        $result = $this->householdService->createHousehold($data);

        if (is_array($result)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Lỗi lưu trữ dữ liệu.',
                'errors'  => $result
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Thêm hộ dân thành công.'
        ]);
    }

    /**
     * Update an existing household.
     */
    public function update($id = null)
    {
        $id = (int)$id;
        $rules = [
            'owner_name'     => 'required|min_length[3]|max_length[255]',
            'address'        => 'required|max_length[255]',
            'household_type' => 'required',
            'status'         => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Vui lòng kiểm tra lại thông tin.',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $data = [
            'owner_name'     => $this->request->getPost('owner_name'),
            'id_card'        => $this->request->getPost('id_card') ?: null,
            'phone'          => $this->request->getPost('phone') ?: null,
            'address'        => $this->request->getPost('address'),
            'ward_group'     => $this->request->getPost('ward_group'),
            'ward'           => $this->request->getPost('ward'),
            'household_type' => $this->request->getPost('household_type'),
            'members_count'  => (int) $this->request->getPost('members_count'),
            'status'         => $this->request->getPost('status'),
            'gps'            => $this->request->getPost('gps') ?: null,
            'route_id'       => $this->request->getPost('route_id') ? (int) $this->request->getPost('route_id') : null,
        ];

        $result = $this->householdService->updateHousehold($id, $data);

        if (is_array($result)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Lỗi cập nhật dữ liệu.',
                'errors'  => $result
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Cập nhật thông tin hộ dân thành công.'
        ]);
    }

    /**
     * Delete a household.
     */
    public function delete($id = null)
    {
        $id = (int)$id;
        $result = $this->householdService->deleteHousehold($id);

        if (!$result) {
            return $this->respond([
                'status'  => false,
                'message' => 'Hộ dân không tồn tại hoặc lỗi xử lý.'
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Xóa hộ dân thành công.'
        ]);
    }

    /**
     * Restore a household from trash.
     */
    public function restore($id = null)
    {
        $id = (int)$id;
        $result = $this->householdService->restoreHousehold($id);

        if (!$result) {
            return $this->respond([
                'status'  => false,
                'message' => 'Lỗi khôi phục hộ dân.'
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Khôi phục hộ dân thành công.'
        ]);
    }

    /**
     * Import households from uploaded Excel/CSV file.
     */
    public function importExcel()
    {
        $file = $this->request->getFile('import_file');

        if (!$file || !$file->isValid()) {
            return $this->respond(['status' => false, 'message' => 'Vui lòng chọn file hợp lệ (.xlsx, .xls, .csv).']);
        }

        $ext = strtolower($file->getExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return $this->respond(['status' => false, 'message' => 'Chỉ hỗ trợ định dạng .xlsx, .xls hoặc .csv.']);
        }

        $tmpPath = WRITEPATH . 'uploads/import_households_' . time() . '.' . $ext;
        $file->move(WRITEPATH . 'uploads/', basename($tmpPath));

        $result = $this->importService->importHouseholds($tmpPath);
        @unlink($tmpPath);

        if (!$result['success']) {
            return $this->respond([
                'status'  => false,
                'message' => 'Import thất bại.',
                'errors'  => $result['errors']
            ]);
        }

        return $this->respond([
            'status'   => true,
            'message'  => "Đã import thành công {$result['imported']} hộ dân.",
            'warnings' => $result['errors'] // non-fatal row errors
        ]);
    }

    /**
     * Download sample import CSV template for households.
     */
    public function downloadTemplate()
    {
        $csvContent = $this->importService->generateHouseholdsTemplate();
        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="mau_import_hodân.csv"')
            ->setBody($csvContent);
    }
}
