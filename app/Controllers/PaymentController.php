<?php

namespace App\Controllers;

use App\Services\PaymentService;
use App\Services\RouteService;
use App\Services\VnptInvoiceService;
use App\Models\HouseholdModel;
use App\Models\PaymentModel;
use CodeIgniter\API\ResponseTrait;

class PaymentController extends BaseController
{
    use ResponseTrait;

    protected PaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
    }

    /**
     * View billing dashboard list of households.
     */
    public function index()
    {
        $routeService = new RouteService();
        $feeRateModel = new \App\Models\FeeRateModel();

        $data = [
            // Return only active child routes
            'routes'   => $routeService->getRoutesList(),
            'feeRates' => $feeRateModel->where('status', 'Đang hiệu lực')->findAll()
        ];
        return view('payment/index', $data);
    }

    /**
     * Return JSON list of households with their latest range payment status.
     */
    public function list()
    {
        $search  = $this->request->getGet('search') ?: null;
        $routeId = $this->request->getGet('route_id') ? (int)$this->request->getGet('route_id') : null;
        $status  = $this->request->getGet('status') ?: null;
        $page    = $this->request->getGet('page') ? (int)$this->request->getGet('page') : 1;
        $perPage = $this->request->getGet('per_page') ? (int)$this->request->getGet('per_page') : 10;

        $result = $this->paymentService->getHouseholdsBillingList($search, $routeId, $status, $page, $perPage);

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
     * Get range billing history for a specific household.
     */
    public function history($id = null)
    {
        $id = (int)$id;
        $history = $this->paymentService->getHouseholdHistory($id);

        return $this->respond([
            'status' => true,
            'data'   => $history
        ]);
    }

    /**
     * Process range payment or invoicing (individual or bulk).
     */
    public function process()
    {
        $rules = [
            'household_ids' => 'required',
            'year'          => 'required|numeric',
            'from_month'    => 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[12]',
            'to_month'      => 'required|numeric|greater_than_equal_to[1]|less_than_equal_to[12]',
            'action'        => 'required|in_list[pay,invoice]'
        ];

        if (!$this->validate($rules)) {
            return $this->respond([
                'status'  => false,
                'message' => 'Dữ liệu nhập vào không hợp lệ hoặc thiếu thông tin.',
                'errors'  => $this->validator->getErrors()
            ]);
        }

        $hIds = $this->request->getPost('household_ids');
        if (!is_array($hIds)) {
            $hIds = [$hIds];
        }
        $hIds = array_map('intval', $hIds);

        $year      = (int)$this->request->getPost('year');
        $fromMonth = (int)$this->request->getPost('from_month');
        $toMonth   = (int)$this->request->getPost('to_month');
        $action    = $this->request->getPost('action');

        if ($toMonth < $fromMonth) {
            return $this->respond([
                'status'  => false,
                'message' => 'Tháng kết thúc phải lớn hơn hoặc bằng tháng bắt đầu.',
                'errors'  => ['to_month' => 'Tháng kết thúc không hợp lệ.']
            ]);
        }

        $res = $this->paymentService->processRangePayment($hIds, $year, $fromMonth, $toMonth, $action);

        return $this->respond($res);
    }

    /**
     * Single invoice print handler.
     */
    public function receipt($id = null)
    {
        $id = (int)$id;
        $year      = $this->request->getGet('year') ? (int)$this->request->getGet('year') : (int)date('Y');
        $fromMonth = $this->request->getGet('from') ? (int)$this->request->getGet('from') : 1;
        $toMonth   = $this->request->getGet('to') ? (int)$this->request->getGet('to') : 12;

        return $this->renderPrintPage([$id], $year, $fromMonth, $toMonth, 'invoice');
    }

    /**
     * Bulk print handler.
     */
    public function bulkPrint()
    {
        $hIds = $this->request->getPost('household_ids');
        if (empty($hIds)) {
            return 'Không có hộ dân nào được chọn để in.';
        }
        if (!is_array($hIds)) {
            $hIds = [$hIds];
        }
        $hIds = array_map('intval', $hIds);

        $year      = (int)$this->request->getPost('year');
        $fromMonth = (int)$this->request->getPost('from_month');
        $toMonth   = (int)$this->request->getPost('to_month');
        $printType = $this->request->getPost('print_type') ?: 'invoice'; // 'receipt' or 'invoice'

        return $this->renderPrintPage($hIds, $year, $fromMonth, $toMonth, $printType);
    }

    /**
     * Render printable receipt/invoice format sheets.
     */
    protected function renderPrintPage(array $hIds, int $year, int $fromMonth, int $toMonth, string $printType)
    {
        $householdModel = new HouseholdModel();
        $paymentModel   = new PaymentModel();
        $db             = \Config\Database::connect();

        // Read system configuration
        $configPath = WRITEPATH . 'system_config.json';
        $config = [];
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
        }
        if (empty($config)) {
            $config = [
                'company_name'  => 'Ban Quản Lý Vệ Sinh Môi Trường Phường Nguyễn Du',
                'company_phone' => '024.3999888',
                'company_email' => 'bql.nguyendu@hanoi.gov.vn'
            ];
        }

        $fromStr = $year . '-' . str_pad($fromMonth, 2, '0', STR_PAD_LEFT);
        $toStr = $year . '-' . str_pad($toMonth, 2, '0', STR_PAD_LEFT);

        $printData = [];

        foreach ($hIds as $hId) {
            $household = $householdModel->select('households.*, collection_routes.route_name')
                ->join('collection_routes', 'collection_routes.id = households.route_id', 'left')
                ->where('households.id', $hId)
                ->first();

            if (!$household) continue;

            // Fetch payment range record covering this exact range, or fallback to overlapping
            $payment = $paymentModel->select('payments.*, users.fullname as collector_name')
                ->join('users', 'users.id = payments.collected_by', 'left')
                ->where('payments.household_id', $hId)
                ->where('payments.billing_from_month', $fromStr)
                ->where('payments.billing_to_month', $toStr)
                ->first();

            if (!$payment) {
                // Check if any overlapping record is found
                $payment = $paymentModel->select('payments.*, users.fullname as collector_name')
                    ->join('users', 'users.id = payments.collected_by', 'left')
                    ->where('payments.household_id', $hId)
                    ->groupStart()
                        ->where('billing_from_month <=', $toStr)
                        ->where('billing_to_month >=', $fromStr)
                    ->groupEnd()
                    ->orderBy('id', 'DESC')
                    ->first();
            }

            if (!$payment) {
                // Draft fallback: compute price & vat for this type
                $rate = $db->table('fee_rates')->where('household_type', $household['household_type'])->where('status', 'Đang hiệu lực')->get()->getRowArray();
                $price = $rate ? (float)$rate['price'] : 30000.00;
                $vat = $rate ? (float)$rate['vat'] : 10.00;
                
                $monthsCount = ($toMonth - $fromMonth) + 1;
                $subtotal = $price * $monthsCount;
                $taxAmount = $subtotal * ($vat / 100);
                $totalAmount = $subtotal + $taxAmount;

                $latestReceipt = 'DỰ THẢO';
                $collectorName = 'Thu ngân viên';
                $paymentDate = date('Y-m-d H:i:s');
                $statusText = 'Chưa thu tiền';
                $savedVat = $vat;
                $savedPrice = $price;
            } else {
                $totalAmount = (float)$payment['amount'];
                $latestReceipt = $payment['receipt_code'];
                $collectorName = $payment['collector_name'] ?? 'Thu ngân viên';
                $paymentDate = $payment['payment_date'];
                $statusText = $payment['payment_status'];

                $rate = $db->table('fee_rates')->where('id', $payment['fee_rate_id'])->get()->getRowArray();
                $savedVat = $rate ? (float)$rate['vat'] : 10.00;
                $savedPrice = $rate ? (float)$rate['price'] : 30000.00;
            }

            $printData[] = [
                'household'      => $household,
                'from_month'     => $fromMonth,
                'to_month'       => $toMonth,
                'year'           => $year,
                'price'          => $savedPrice,
                'vat'            => $savedVat,
                'total_amount'   => $totalAmount,
                'receipt_code'   => $latestReceipt,
                'collector_name' => $collectorName,
                'payment_date'   => $paymentDate,
                'status_text'    => $statusText,
                'print_type'     => $printType
            ];
        }

        return view('payment/receipt', [
            'printData' => $printData,
            'config'    => $config
        ]);
    }

    /**
     * Print a specific payment record (receipt or invoice).
     */
    public function printRecord($id = null)
    {
        $id = (int)$id;
        $printType = $this->request->getGet('type') === 'receipt' ? 'receipt' : 'invoice';

        $paymentModel = new \App\Models\PaymentModel();
        $householdModel = new \App\Models\HouseholdModel();
        $db = \Config\Database::connect();

        $payment = $paymentModel->select('payments.*, users.fullname as collector_name')
            ->join('users', 'users.id = payments.collected_by', 'left')
            ->where('payments.id', $id)
            ->first();

        if (!$payment) {
            return 'Không tìm thấy thông tin phiếu thu này.';
        }

        $household = $householdModel->select('households.*, collection_routes.route_name')
            ->join('collection_routes', 'collection_routes.id = households.route_id', 'left')
            ->where('households.id', $payment['household_id'])
            ->first();

        if (!$household) {
            return 'Không tìm thấy thông tin hộ dân tương ứng.';
        }

        // Parse billing from/to
        $fromParts = explode('-', $payment['billing_from_month']);
        $toParts = explode('-', $payment['billing_to_month']);
        
        $year = isset($fromParts[0]) ? (int)$fromParts[0] : (int)date('Y');
        $fromMonth = isset($fromParts[1]) ? (int)$fromParts[1] : 1;
        $toMonth = isset($toParts[1]) ? (int)$toParts[1] : 12;

        $rate = $db->table('fee_rates')->where('id', $payment['fee_rate_id'])->get()->getRowArray();
        $savedVat = $rate ? (float)$rate['vat'] : 10.00;
        $savedPrice = $rate ? (float)$rate['price'] : 30000.00;

        // Read system configuration
        $configPath = WRITEPATH . 'system_config.json';
        $config = [];
        if (file_exists($configPath)) {
            $config = json_decode(file_get_contents($configPath), true);
        }
        if (empty($config)) {
            $config = [
                'company_name'  => 'Ban Quản Lý Vệ Sinh Môi Trường Phường Nguyễn Du',
                'company_phone' => '024.3999888',
                'company_email' => 'bql.nguyendu@hanoi.gov.vn'
            ];
        }

        $printData = [[
            'household'      => $household,
            'from_month'     => $fromMonth,
            'to_month'       => $toMonth,
            'year'           => $year,
            'price'          => $savedPrice,
            'vat'            => $savedVat,
            'total_amount'   => (float)$payment['amount'],
            'receipt_code'   => $payment['receipt_code'],
            'collector_name' => $payment['collector_name'] ?? 'Thu ngân viên',
            'payment_date'   => $payment['payment_date'],
            'status_text'    => $payment['payment_status'],
            'print_type'     => $printType
        ]];

        return view('payment/receipt', [
            'printData' => $printData,
            'config'    => $config
        ]);
    }

    /**
     * Publish a payment as a VNPT electronic invoice (HĐĐT).
     * POST /payments/publish-invoice/(:num)
     */
    public function publishInvoice($id = null)
    {
        $id = (int)$id;
        if ($id <= 0) {
            return $this->respond(['status' => false, 'message' => 'ID không hợp lệ.']);
        }

        $vnptService = new VnptInvoiceService();
        $result = $vnptService->publishInvoice($id);

        return $this->respond([
            'status'  => $result['success'],
            'message' => $result['message'],
            'inv_no'  => $result['inv_no'] ?? null,
        ]);
    }

    /**
     * Debug endpoint: test raw VNPT API connection and return full response.
     * GET /payments/vnpt-debug
     * Only accessible to admin role.
     */
    public function vnptDebug()
    {
        if (session()->get('role') !== 'Admin') {
            return $this->respond(['error' => 'Unauthorized'], 403);
        }

        $vnptService = new VnptInvoiceService();

        // Build a minimal test invoice XML
        $fakePayment = [
            'id'                => 0,
            'billing_from_month'=> date('Y') . '-01',
            'billing_to_month'  => date('Y') . '-01',
            'payment_date'      => date('Y-m-d H:i:s'),
            'amount'            => 33000,
            'fee_rate_id'       => null,
            'receipt_code'      => 'TEST-DEBUG-' . time(),
            'collector_name'    => 'Test',
        ];
        $fakeHousehold = [
            'household_code' => 'HD00001',
            'owner_name'     => 'Nguyễn Văn A (Test)',
            'address'        => 'Số 1 Phố Test',
            'route_name'     => 'Test Route',
        ];

        $fkey  = $fakePayment['receipt_code'];
        $xmlInv = $vnptService->buildInvXml($fakePayment, $fakeHousehold, $fkey);

        // Use reflection to call protected callPublishService
        $ref = new \ReflectionClass($vnptService);
        $method = $ref->getMethod('callPublishService');
        $method->setAccessible(true);
        $soapResult = $method->invoke($vnptService, $xmlInv);

        // Also read debug log if exists
        $logPath = WRITEPATH . 'logs/vnpt_debug.log';
        $logContent = file_exists($logPath) ? file_get_contents($logPath) : 'Log file not found.';
        $logTail = implode("\n", array_slice(explode("\n", $logContent), -60));

        return $this->response
            ->setContentType('text/plain; charset=utf-8')
            ->setBody(
                "=== SOAP Result ===\n" . print_r($soapResult, true) .
                "\n\n=== Sent XML ===\n" . $xmlInv .
                "\n\n=== Last 60 lines of vnpt_debug.log ===\n" . $logTail
            );
    }
}
