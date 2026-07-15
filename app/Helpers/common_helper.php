<?php

/**
 * Check if the logged-in user has a specific permission.
 *
 * @param string $permissionKey
 * @return bool
 */
if (!function_exists('has_permission')) {
    function has_permission(string $permissionKey): bool
    {
        $session = session();
        if (!$session->get('is_logged_in')) {
            return false;
        }

        $role = $session->get('role');
        if ($role === 'Super Admin') {
            return true;
        }

        $db = \Config\Database::connect();
        return $db->table('role_permissions')
            ->join('permissions', 'permissions.id = role_permissions.permission_id')
            ->where('role_permissions.role', $role)
            ->where('permissions.permission_key', $permissionKey)
            ->countAllResults() > 0;
    }
}

/**
 * Format money to VND currency format.
 *
 * @param float|int $amount
 * @return string
 */
if (!function_exists('format_money')) {
    function format_money($amount): string
    {
        return number_format((double)$amount, 0, ',', '.') . ' VNĐ';
    }
}

/**
 * Format date to standard display format.
 *
 * @param string|null $date
 * @param string $format
 * @return string
 */
if (!function_exists('format_date')) {
    function format_date(?string $date, string $format = 'd/m/Y H:i'): string
    {
        if (empty($date)) {
            return '-';
        }
        return date($format, strtotime($date));
    }
}
