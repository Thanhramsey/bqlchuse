<?php

namespace App\Services;

use App\Models\HouseholdModel;
use App\Models\CollectionRouteModel;
use App\Models\FeeRateModel;

class HouseholdService
{
    protected HouseholdModel $householdModel;

    public function __construct()
    {
        $this->householdModel = new HouseholdModel();
    }

    public function getHouseholdsList(?string $search = null, ?int $routeId = null, int $page = 1, int $perPage = 10, bool $showDeleted = false): array
    {
        $query = $this->householdModel->select('households.*, collection_routes.route_name')
            ->join('collection_routes', 'collection_routes.id = households.route_id', 'left');

        if (session()->get('role') === 'Nhân viên') {
            $userId = (int)session()->get('user_id');
            $db = \Config\Database::connect();
            $assigned = $db->table('route_assignments')
                ->where('user_id', $userId)
                ->get()
                ->getResultArray();
            $directIds = array_column($assigned, 'route_id');
            
            if (empty($directIds)) {
                return ['list' => [], 'total' => 0];
            }
            
            $children = $db->table('collection_routes')
                ->select('id')
                ->whereIn('parent_id', $directIds)
                ->get()
                ->getResultArray();
            $childIds = array_column($children, 'id');
            
            $allowedRouteIds = array_unique(array_merge($directIds, $childIds));
            
            $query->whereIn('households.route_id', $allowedRouteIds);
        }

        if ($showDeleted) {
            $query->onlyDeleted();
        }

        if (!empty($search)) {
            $query->groupStart()
                ->like('households.owner_name', $search)
                ->orLike('households.household_code', $search)
                ->orLike('households.address', $search)
            ->groupEnd();
        }

        if (!empty($routeId)) {
            $query->where('households.route_id', $routeId);
        }

        $total = $query->countAllResults(false);
        $offset = ($page - 1) * $perPage;
        $list = $query->orderBy('households.id', 'DESC')->limit($perPage, $offset)->findAll();

        return [
            'list'  => $list,
            'total' => $total
        ];
    }

    /**
     * Get list of collection routes for dropdown selection.
     */
    public function getRoutesList()
    {
        $routeModel = new CollectionRouteModel();
        return $routeModel->where('status', 'Hoạt động')->where('parent_id !=', null)->findAll();
    }

    /**
     * Get list of fee rates (household types) for dropdown selection.
     */
    public function getFeeRatesList()
    {
        $rateModel = new FeeRateModel();
        return $rateModel->where('status', 'Đang hiệu lực')->findAll();
    }

    /**
     * Generate the next unique household code (e.g. HD00005).
     */
    public function generateHouseholdCode(): string
    {
        $lastRow = $this->householdModel->orderBy('id', 'DESC')->first();
        if (!$lastRow) {
            return 'HD00001';
        }
        $lastId = $lastRow['id'];
        $nextId = $lastId + 1;
        return 'HD' . str_pad($nextId, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Create a new household.
     */
    public function createHousehold(array $data)
    {
        $data['household_code'] = $this->generateHouseholdCode();
        
        if (!$this->householdModel->insert($data)) {
            return $this->householdModel->errors();
        }

        LogService::log('Thêm', 'Quản lý hộ dân', "Thêm hộ dân mới {$data['household_code']} - {$data['owner_name']}");
        return true;
    }

    /**
     * Update an existing household.
     */
    public function updateHousehold(int $id, array $data)
    {
        // Don't update code, let it be same
        unset($data['household_code']);
        
        if (!$this->householdModel->update($id, $data)) {
            return $this->householdModel->errors();
        }

        $household = $this->householdModel->find($id);
        LogService::log('Sửa', 'Quản lý hộ dân', "Cập nhật hộ dân {$household['household_code']} - {$household['owner_name']}");
        return true;
    }

    /**
     * Delete a household (soft delete).
     */
    public function deleteHousehold(int $id)
    {
        $household = $this->householdModel->find($id);
        if (!$household) {
            return false;
        }

        if (!$this->householdModel->delete($id)) {
            return false;
        }

        LogService::log('Xóa', 'Quản lý hộ dân', "Xóa hộ dân {$household['household_code']} - {$household['owner_name']}");
        return true;
    }

    /**
     * Restore a soft-deleted household.
     */
    public function restoreHousehold(int $id): bool
    {
        $db = \Config\Database::connect();
        // Set deleted_at directly to null via Query Builder
        $db->table('households')->where('id', $id)->update(['deleted_at' => null]);
        
        $household = $this->householdModel->withDeleted()->find($id);
        if ($household) {
            LogService::log('Khôi phục', 'Quản lý hộ dân', "Khôi phục hộ dân đã xóa: {$household['household_code']} - {$household['owner_name']}");
            return true;
        }
        return false;
    }
}
