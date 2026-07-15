<?php

namespace App\Controllers;

use App\Services\RouteService;
use App\Services\ImportService;
use CodeIgniter\API\ResponseTrait;

class RouteController extends BaseController
{
    use ResponseTrait;

    protected RouteService $routeService;
    protected ImportService $importService;

    public function __construct()
    {
        $this->routeService = new RouteService();
        $this->importService = new ImportService();
    }

    /**
     * View routes management.
     */
    public function index()
    {
        $data = [
            'parents' => $this->routeService->getParentRoutes(),
            'staff'   => $this->routeService->getCollectStaff(),
        ];
        return view('route/index', $data);
    }

    /**
     * Fetch JSON list of routes.
     */
    public function list()
    {
        $userId = null;
        if (session()->get('role') === 'Nhân viên') {
            $userId = (int)session()->get('user_id');
        }

        $list = $this->routeService->getRoutesList($userId);
        
        // Dynamic parent routes mapping for convenience
        // Pass parent options context to client side
        $parents = $this->routeService->getParentRoutes();

        return $this->respond([
            'status'  => true,
            'data'    => $list,
            'parents' => $parents
        ]);
    }

    /**
     * Create route.
     */
    public function create()
    {
        $rules = [
            'route_code' => 'required|max_length[50]|is_unique[collection_routes.route_code]',
            'route_name' => 'required|max_length[255]',
            'status'     => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Dữ liệu nhập vào không hợp lệ.',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $data = [
            'route_code' => $this->request->getPost('route_code'),
            'route_name' => $this->request->getPost('route_name'),
            'parent_id'  => $this->request->getPost('parent_id') ? (int) $this->request->getPost('parent_id') : null,
            'status'     => $this->request->getPost('status'),
        ];

        $staffIds = $this->request->getPost('assigned_staff_ids') ?: [];

        $result = $this->routeService->createRoute($data, $staffIds);

        if (!$result) {
            return $this->respond([
                'status'  => false,
                'message' => 'Lỗi lưu trữ dữ liệu hoặc trùng mã tuyến.'
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Thêm tuyến thu gom thành công.'
        ]);
    }

    /**
     * Update route.
     */
    public function update($id = null)
    {
        $id = (int)$id;
        $rules = [
            'route_code' => "required|max_length[50]|is_unique[collection_routes.route_code,id,{$id}]",
            'route_name' => 'required|max_length[255]',
            'status'     => 'required'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Dữ liệu nhập vào không hợp lệ.',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $data = [
            'route_code' => $this->request->getPost('route_code'),
            'route_name' => $this->request->getPost('route_name'),
            'parent_id'  => $this->request->getPost('parent_id') ? (int) $this->request->getPost('parent_id') : null,
            'status'     => $this->request->getPost('status'),
        ];

        // Self-reference check
        if ($data['parent_id'] === $id) {
            return $this->respond([
                'status'  => false,
                'message' => 'Tuyến thu gom không thể làm tuyến cha của chính nó.',
                'errors'  => ['parent_id' => 'Tuyến cha không hợp lệ.']
            ]);
        }

        $staffIds = $this->request->getPost('assigned_staff_ids') ?: [];

        $result = $this->routeService->updateRoute($id, $data, $staffIds);

        if (!$result) {
            return $this->respond([
                'status'  => false,
                'message' => 'Lỗi cập nhật dữ liệu.'
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Cập nhật tuyến thu gom thành công.'
        ]);
    }

    /**
     * Delete route.
     */
    public function delete($id = null)
    {
        $id = (int)$id;
        $result = $this->routeService->deleteRoute($id);

        if (!$result) {
            return $this->respond([
                'status'  => false,
                'message' => 'Tuyến thu gom không tồn tại.'
            ]);
        }

        return $this->respond([
            'status'  => true,
            'message' => 'Xóa tuyến thu gom thành công.'
        ]);
    }

    /**
     * Import collection routes from uploaded Excel/CSV file.
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

        $tmpPath = WRITEPATH . 'uploads/import_routes_' . time() . '.' . $ext;
        $file->move(WRITEPATH . 'uploads/', basename($tmpPath));

        $result = $this->importService->importRoutes($tmpPath);
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
            'message'  => "Đã import thành công {$result['imported']} tuyến thu gom.",
            'warnings' => $result['errors']
        ]);
    }

    /**
     * Download sample CSV template for routes import.
     */
    public function downloadTemplate()
    {
        $csvContent = $this->importService->generateRoutesTemplate();
        return $this->response
            ->setHeader('Content-Type', 'text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="mau_import_tuyenthu.csv"')
            ->setBody($csvContent);
    }
}
