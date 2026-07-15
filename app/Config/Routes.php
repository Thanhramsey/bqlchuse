<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'DashboardController::index', ['filter' => 'auth']);
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attemptLogin');
$routes->get('logout', 'AuthController::logout');

$routes->group('', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'DashboardController::index', ['filter' => 'permission:dashboard.view']);

    // Households
    $routes->group('households', function($routes) {
        $routes->get('', 'HouseholdController::index', ['filter' => 'permission:households.view']);
        $routes->get('list', 'HouseholdController::list', ['filter' => 'permission:households.view']);
        $routes->post('create', 'HouseholdController::create', ['filter' => 'permission:households.create']);
        $routes->post('update/(:num)', 'HouseholdController::update/$1', ['filter' => 'permission:households.edit']);
        $routes->post('delete/(:num)', 'HouseholdController::delete/$1', ['filter' => 'permission:households.delete']);
        $routes->post('restore/(:num)', 'HouseholdController::restore/$1', ['filter' => 'permission:households.create']);
        $routes->post('import', 'HouseholdController::importExcel', ['filter' => 'permission:households.create']);
        $routes->get('template', 'HouseholdController::downloadTemplate', ['filter' => 'permission:households.create']);
    });



    // Routes
    $routes->group('routes', function($routes) {
        $routes->get('', 'RouteController::index', ['filter' => 'permission:routes.view']);
        $routes->get('list', 'RouteController::list', ['filter' => 'permission:routes.view']);
        $routes->post('create', 'RouteController::create', ['filter' => 'permission:routes.create']);
        $routes->post('update/(:num)', 'RouteController::update/$1', ['filter' => 'permission:routes.edit']);
        $routes->post('delete/(:num)', 'RouteController::delete/$1', ['filter' => 'permission:routes.delete']);
        $routes->post('import', 'RouteController::importExcel', ['filter' => 'permission:routes.create']);
        $routes->get('template', 'RouteController::downloadTemplate', ['filter' => 'permission:routes.create']);
    });

    // Employees
    $routes->group('employees', function($routes) {
        $routes->get('', 'EmployeeController::index', ['filter' => 'permission:employees.view']);
        $routes->get('list', 'EmployeeController::list', ['filter' => 'permission:employees.view']);
        $routes->post('create', 'EmployeeController::create', ['filter' => 'permission:employees.create']);
        $routes->post('update/(:num)', 'EmployeeController::update/$1', ['filter' => 'permission:employees.edit']);
        $routes->post('delete/(:num)', 'EmployeeController::delete/$1', ['filter' => 'permission:employees.delete']);
    });

    // Fee rates
    $routes->group('fee-rates', function($routes) {
        $routes->get('', 'FeeRateController::index', ['filter' => 'permission:fee_rates.view']);
        $routes->get('list', 'FeeRateController::list', ['filter' => 'permission:fee_rates.view']);
        $routes->post('create', 'FeeRateController::create', ['filter' => 'permission:fee_rates.create']);
        $routes->post('update/(:num)', 'FeeRateController::update/$1', ['filter' => 'permission:fee_rates.edit']);
        $routes->post('delete/(:num)', 'FeeRateController::delete/$1', ['filter' => 'permission:fee_rates.delete']);
    });

    // Payments & Invoicing
    $routes->group('payments', function($routes) {
        $routes->get('', 'PaymentController::index', ['filter' => 'permission:payments.view']);
        $routes->get('list', 'PaymentController::list', ['filter' => 'permission:payments.view']);
        $routes->get('history/(:num)', 'PaymentController::history/$1', ['filter' => 'permission:payments.view']);
        $routes->post('process', 'PaymentController::process', ['filter' => 'permission:payments.create']);
        $routes->post('bulk-print', 'PaymentController::bulkPrint', ['filter' => 'permission:payments.view']);
        $routes->get('receipt/(:num)', 'PaymentController::receipt/$1', ['filter' => 'permission:payments.view']);
        $routes->get('print-record/(:num)', 'PaymentController::printRecord/$1', ['filter' => 'permission:payments.view']);
        $routes->post('publish-invoice/(:num)', 'PaymentController::publishInvoice/$1', ['filter' => 'permission:payments.create']);
        $routes->get('vnpt-debug', 'PaymentController::vnptDebug', ['filter' => 'permission:config.view']);
    });

    // Debts
    $routes->group('debts', function($routes) {
        $routes->get('', 'PaymentController::debts', ['filter' => 'permission:debts.view']);
        $routes->post('remind/(:num)', 'PaymentController::remind/$1', ['filter' => 'permission:debts.remind']);
    });

    // Reports
    $routes->group('reports', function($routes) {
        $routes->get('', 'ReportController::index', ['filter' => 'permission:reports.view']);
        $routes->get('revenue', 'ReportController::revenueData', ['filter' => 'permission:reports.view']);
        $routes->get('export/excel', 'ReportController::exportExcel', ['filter' => 'permission:reports.export']);
        $routes->get('export/pdf', 'ReportController::exportPdf', ['filter' => 'permission:reports.export']);
    });

    // System Logs
    $routes->group('logs', function($routes) {
        $routes->get('', 'DashboardController::logs', ['filter' => 'permission:config.view']);
    });

    // System Config
    $routes->group('config', function($routes) {
        $routes->get('', 'DashboardController::config', ['filter' => 'permission:config.view']);
        $routes->post('update', 'DashboardController::updateConfig', ['filter' => 'permission:config.edit']);
    });
});
