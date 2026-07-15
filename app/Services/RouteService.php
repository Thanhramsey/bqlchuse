<?php

namespace App\Services;

use App\Models\CollectionRouteModel;
use App\Models\UserModel;

class RouteService
{
    protected CollectionRouteModel $routeModel;

    public function __construct()
    {
        $this->routeModel = new CollectionRouteModel();
    }

    /**
     * Get all routes with parent route names and assigned staff.
     */
    public function getRoutesList(?int $onlyAssignedUserId = null): array
    {
        $db = \Config\Database::connect();
        $allowedRouteIds = [];
        if ($onlyAssignedUserId !== null) {
            // Find directly assigned routes
            $assigned = $db->table('route_assignments')
                ->where('user_id', $onlyAssignedUserId)
                ->get()
                ->getResultArray();
            $directIds = array_column($assigned, 'route_id');
            
            if (empty($directIds)) {
                return [];
            }
            
            // Find child routes
            $children = $this->routeModel->select('id')
                ->whereIn('parent_id', $directIds)
                ->findAll();
            $childIds = array_column($children, 'id');
            
            $allowedRouteIds = array_unique(array_merge($directIds, $childIds));
        }

        $query = $this->routeModel->select('collection_routes.*, p.route_name as parent_name')
            ->join('collection_routes p', 'p.id = collection_routes.parent_id', 'left');

        if ($onlyAssignedUserId !== null) {
            $query->whereIn('collection_routes.id', $allowedRouteIds);
        }

        $routes = $query->orderBy('collection_routes.parent_id', 'ASC')
            ->orderBy('collection_routes.id', 'ASC')
            ->findAll();

        $assignments = $db->table('route_assignments')
            ->select('route_assignments.route_id, users.id as user_id, users.fullname, users.role')
            ->join('users', 'users.id = route_assignments.user_id')
            ->get()
            ->getResultArray();

        // Group assignments by route_id
        $assignMap = [];
        foreach ($assignments as $a) {
            $assignMap[$a['route_id']][] = [
                'id'       => $a['user_id'],
                'fullname' => $a['fullname'],
                'role'     => $a['role']
            ];
        }

        // Merge assignments into routes
        foreach ($routes as &$r) {
            $r['assigned_staff'] = $assignMap[$r['id']] ?? [];
            $r['assigned_staff_ids'] = array_column($r['assigned_staff'], 'id');
        }

        return $routes;
    }

    /**
     * Get list of root/parent routes (where parent_id is null).
     */
    public function getParentRoutes(?int $excludeId = null): array
    {
        $query = $this->routeModel->where('parent_id', null)->where('status', 'Hoạt động');
        if ($excludeId !== null) {
            $query->where('id !=', $excludeId);
        }
        return $query->findAll();
    }

    /**
     * Get list of cashiers/collectors for assignment dropdowns/checkboxes.
     */
    public function getCollectStaff(): array
    {
        $userModel = new UserModel();
        return $userModel->whereIn('role', ['Thu ngân', 'Nhân viên'])
            ->where('status', 'Hoạt động')
            ->findAll();
    }

    /**
     * Create a route and assign staff.
     */
    public function createRoute(array $data, array $staffIds): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        $routeId = $this->routeModel->insert($data);
        if (!$routeId) {
            $db->transRollback();
            return false;
        }

        // Save assignments
        if (!empty($staffIds)) {
            $assignData = [];
            foreach ($staffIds as $uid) {
                $assignData[] = [
                    'route_id' => $routeId,
                    'user_id'  => (int) $uid
                ];
            }
            $db->table('route_assignments')->insertBatch($assignData);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return false;
        }

        LogService::log('Thêm', 'Tuyến thu gom', "Thêm tuyến thu gom mới: {$data['route_code']} - {$data['route_name']}");
        return true;
    }

    /**
     * Update a route and sync staff assignments.
     */
    public function updateRoute(int $id, array $data, array $staffIds): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        if (!$this->routeModel->update($id, $data)) {
            $db->transRollback();
            return false;
        }

        // Sync assignments: delete old ones and insert new ones
        $db->table('route_assignments')->where('route_id', $id)->delete();
        
        if (!empty($staffIds)) {
            $assignData = [];
            foreach ($staffIds as $uid) {
                $assignData[] = [
                    'route_id' => $id,
                    'user_id'  => (int) $uid
                ];
            }
            $db->table('route_assignments')->insertBatch($assignData);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return false;
        }

        LogService::log('Sửa', 'Tuyến thu gom', "Cập nhật tuyến thu gom: {$data['route_name']}");
        return true;
    }

    /**
     * Delete a route (soft delete).
     */
    public function deleteRoute(int $id): bool
    {
        $route = $this->routeModel->find($id);
        if (!$route) {
            return false;
        }

        if (!$this->routeModel->delete($id)) {
            return false;
        }

        LogService::log('Xóa', 'Tuyến thu gom', "Xóa tuyến thu gom: {$route['route_code']} - {$route['route_name']}");
        return true;
    }
}
