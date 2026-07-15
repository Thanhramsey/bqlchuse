<?php

namespace App\Services;

use App\Models\PaymentModel;
use App\Models\HouseholdModel;

/**
 * VnptInvoiceService
 *
 * Handles communication with the VNPT PublishService SOAP API
 * to import and publish electronic invoices (hóa đơn điện tử - HĐĐT).
 *
 * API used: ImportAndPublishInv
 * Endpoint: PublishService.asmx
 */
class VnptInvoiceService
{
    protected array $config;
    protected PaymentModel $paymentModel;
    protected HouseholdModel $householdModel;

    public function __construct()
    {
        $this->paymentModel   = new PaymentModel();
        $this->householdModel = new HouseholdModel();
        $this->config         = $this->loadConfig();
    }

    // -------------------------------------------------------------------------
    // Config helpers
    // -------------------------------------------------------------------------

    protected function loadConfig(): array
    {
        $path = WRITEPATH . 'system_config.json';
        if (file_exists($path)) {
            $cfg = json_decode(file_get_contents($path), true);
            if (is_array($cfg)) {
                return $cfg;
            }
        }
        return [];
    }

    protected function cfgGet(string $key, string $default = ''): string
    {
        return (string)($this->config[$key] ?? $default);
    }

    // -------------------------------------------------------------------------
    // Main public method
    // -------------------------------------------------------------------------

    /**
     * Publish a single payment record as an VNPT electronic invoice.
     *
     * @param int $paymentId   ID of the payments table record
     * @return array           ['success' => bool, 'message' => string, 'inv_no' => string|null]
     */
    public function publishInvoice(int $paymentId): array
    {
        // 1. Load payment record
        $db = \Config\Database::connect();
        $payment = $this->paymentModel
            ->select('payments.*, users.fullname as collector_name')
            ->join('users', 'users.id = payments.collected_by', 'left')
            ->where('payments.id', $paymentId)
            ->first();

        if (!$payment) {
            return ['success' => false, 'message' => 'Không tìm thấy bản ghi thanh toán.', 'inv_no' => null];
        }

        if (!empty($payment['vnpt_inv_no'])) {
            return [
                'success' => false,
                'message' => "Hóa đơn này đã được xuất với số hóa đơn VNPT: {$payment['vnpt_inv_no']}.",
                'inv_no'  => $payment['vnpt_inv_no']
            ];
        }

        // 2. Load household
        $household = $this->householdModel
            ->select('households.*, collection_routes.route_name')
            ->join('collection_routes', 'collection_routes.id = households.route_id', 'left')
            ->where('households.id', $payment['household_id'])
            ->first();

        if (!$household) {
            return ['success' => false, 'message' => 'Không tìm thấy thông tin hộ dân.', 'inv_no' => null];
        }

        // 3. Build XML
        $fkey   = 'HD_' . $household['household_code'] . '_' . date('dmYHis');
        $xmlInv = $this->buildInvXml($payment, $household, $fkey);

        // 4. Call VNPT SOAP API
        $soapResult = $this->callPublishService($xmlInv);

        if (!$soapResult['success']) {
            return $soapResult;
        }

        // 5. Parse response
        $parsed = $this->parseResponse($soapResult['raw']);

        if (!$parsed['success']) {
            return ['success' => false, 'message' => $parsed['message'], 'inv_no' => null];
        }

        // 6. Update payment record
        $this->paymentModel->update($paymentId, [
            'vnpt_fkey'       => $fkey,
            'vnpt_inv_no'     => $parsed['inv_no'],
            'vnpt_issue_date' => date('Y-m-d H:i:s'),
            'payment_status'  => 'Đã xuất hóa đơn',
            'updated_at'      => date('Y-m-d H:i:s'),
        ]);

        LogService::log('HĐĐT', 'Xuất hóa đơn điện tử', "Xuất HĐĐT thành công cho payment #{$paymentId} - Hộ: {$household['owner_name']} - Số HĐ: {$parsed['inv_no']}");

        return [
            'success' => true,
            'message' => "Xuất hóa đơn điện tử thành công! Số hóa đơn: <strong>{$parsed['inv_no']}</strong>",
            'inv_no'  => $parsed['inv_no'],
        ];
    }

    // -------------------------------------------------------------------------
    // XML Builder — theo đúng chuẩn cấu trúc VNPT
    // -------------------------------------------------------------------------

    public function buildInvXml(array $payment, array $household, string $fkey): string
    {
        $fromParts = explode('-', $payment['billing_from_month'] ?? '');
        $toParts   = explode('-', $payment['billing_to_month'] ?? '');

        $fromYear  = (int)($fromParts[0] ?? date('Y'));
        $fromM     = (int)($fromParts[1] ?? 1);
        $toYear    = (int)($toParts[0] ?? date('Y'));
        $toM       = (int)($toParts[1] ?? 12);

        $monthsCount = max(1, (($toYear - $fromYear) * 12 + ($toM - $fromM)) + 1);

        // Load fee rate for price/vat
        $db   = \Config\Database::connect();
        $rate = $db->table('fee_rates')
            ->where('id', $payment['fee_rate_id'] ?? 0)
            ->get()->getRowArray();

        // Prices must be integer (VNPT does not accept decimals)
        $price    = $rate ? (int)round((float)$rate['price']) : (int)round((float)$payment['amount'] / ($monthsCount ?: 1) / 1.1);
        $vatRate  = $rate ? (int)round((float)$rate['vat']) : 10;
        $subtotal = $price * $monthsCount;                           // Total before VAT
        $vatAmt   = (int)round($subtotal * $vatRate / 100);          // VAT amount
        $total    = $subtotal + $vatAmt;                             // Grand total

        // Product name: "Phí vệ sinh môi trường tháng MM/YYYY [- MM/YYYY]"
        $prodName = 'Phi ve sinh moi truong thang ' . str_pad($fromM, 2, '0', STR_PAD_LEFT) . '/' . $fromYear;
        if ($fromM !== $toM || $fromYear !== $toYear) {
            $prodName .= ' - ' . str_pad($toM, 2, '0', STR_PAD_LEFT) . '/' . $toYear;
        }

        // KindOfService: ví dụ "PHI VE SINH THANG 01-07/2026"
        $kindOfService = 'PHI VE SINH THANG ' . str_pad($fromM, 2, '0', STR_PAD_LEFT) . '-' . str_pad($toM, 2, '0', STR_PAD_LEFT) . '/' . $fromYear;

        // Sanitize text (no special chars - VNPT may reject UTF-8 in some fields)
        $cusName    = htmlspecialchars($household['owner_name'] ?? '', ENT_XML1, 'UTF-8');
        $cusAddress = htmlspecialchars($household['address'] ?? '', ENT_XML1, 'UTF-8');
        $cusCode    = htmlspecialchars($household['household_code'] ?? '', ENT_XML1, 'UTF-8');
        $prodNameE  = htmlspecialchars($prodName, ENT_XML1, 'UTF-8');
        $kindE      = htmlspecialchars($kindOfService, ENT_XML1, 'UTF-8');
        $fkeyE      = htmlspecialchars($fkey, ENT_XML1, 'UTF-8');
        $amountWords = $this->numberToWords($total);
        $amountWordsE = htmlspecialchars($amountWords, ENT_XML1, 'UTF-8');

        // Build XML exactly per VNPT spec (Minimalistic format verified working)
        // IMPORTANT: <key> inside <Inv> is REQUIRED (*). Omit optional empty tags to avoid ERR:3
        $xml = <<<XML
<Invoices>
  <Inv>
    <key>{$fkeyE}</key>
    <Invoice>
      <CusCode>{$cusCode}</CusCode>
      <CusName>{$cusName}</CusName>
      <CusAddress>{$cusAddress}</CusAddress>
      <PaymentMethod>TM/CK</PaymentMethod>
      <KindOfService>{$kindE}</KindOfService>
      <Products>
        <Product>
          <ProdName>{$prodNameE}</ProdName>
          <ProdUnit>thang</ProdUnit>
          <ProdQuantity>{$monthsCount}</ProdQuantity>
          <ProdPrice>{$price}</ProdPrice>
          <Amount>{$subtotal}</Amount>
        </Product>
      </Products>
      <Total>{$subtotal}</Total>
      <VATRate>{$vatRate}</VATRate>
      <VATAmount>{$vatAmt}</VATAmount>
      <Amount>{$total}</Amount>
      <AmountInWords>{$amountWordsE}</AmountInWords>
    </Invoice>
  </Inv>
</Invoices>
XML;

        return $xml;
    }

    // -------------------------------------------------------------------------
    // SOAP Caller
    // -------------------------------------------------------------------------

    protected function callPublishService(string $xmlInvData): array
    {
        $endpoint = rtrim($this->cfgGet('PUBLISH_SERVICE_ADDRESS_ID', 'https://bvdkphutho-tt78admindemo.vnpt-invoice.com.vn/publishservice.asmx'), '/');
        $account  = $this->cfgGet('C_USER_ID', 'bvdkphuthoadmin_demo');
        $acpass   = $this->cfgGet('C_PASSWORD_ID', '123456aA@78');
        $username = $this->cfgGet('WS_USER_ID', 'wsmsservice');
        $password = $this->cfgGet('WS_PASSWORD_ID', '123456aA@');
        $pattern  = $this->cfgGet('PATTERN_HD_ID', '1/003');
        $serial   = $this->cfgGet('SERIAL_HD_ID', 'C23TAA');

        // Escape the XML data for embedding in SOAP body
        $xmlEscaped = htmlspecialchars($xmlInvData, ENT_XML1, 'UTF-8');

        $soapBody = <<<SOAP
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
               xmlns:xsd="http://www.w3.org/2001/XMLSchema"
               xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
  <soap:Body>
    <ImportAndPublishInv xmlns="http://tempuri.org/">
      <Account>{$account}</Account>
      <ACpass>{$acpass}</ACpass>
      <xmlInvData>{$xmlEscaped}</xmlInvData>
      <username>{$username}</username>
      <password>{$password}</password>
      <pattern>{$pattern}</pattern>
      <serial>{$serial}</serial>
      <convert>0</convert>
    </ImportAndPublishInv>
  </soap:Body>
</soap:Envelope>
SOAP;

        $options = [
            'http' => [
                'method'  => 'POST',
                'header'  => implode("\r\n", [
                    'Content-Type: text/xml; charset=utf-8',
                    'SOAPAction: "http://tempuri.org/ImportAndPublishInv"',
                    'Content-Length: ' . strlen($soapBody),
                ]),
                'content' => $soapBody,
                'timeout' => 30,
            ],
            'ssl' => [
                'verify_peer'      => false,
                'verify_peer_name' => false,
            ],
        ];

        $context = stream_context_create($options);
        $raw     = @file_get_contents($endpoint, false, $context);

        if ($raw === false) {
            $err = error_get_last();
            return [
                'success' => false,
                'message' => 'Không thể kết nối đến máy chủ VNPT: ' . ($err['message'] ?? 'Lỗi không xác định'),
                'raw'     => ''
            ];
        }

        return ['success' => true, 'message' => 'OK', 'raw' => $raw];
    }

    // -------------------------------------------------------------------------
    // Response Parser (regex-based, namespace-safe)
    // -------------------------------------------------------------------------

    protected function parseResponse(string $raw): array
    {
        // Always log raw response for debugging
        $logPath   = WRITEPATH . 'logs/vnpt_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        @file_put_contents($logPath, "\n\n=== [{$timestamp}] VNPT RAW RESPONSE ===\n{$raw}\n", FILE_APPEND);

        if (empty(trim($raw))) {
            return ['success' => false, 'message' => 'VNPT trả về phản hồi rỗng.', 'inv_no' => null];
        }

        // Step 1: Extract the inner result string from SOAP envelope using regex
        $resultStr = null;
        if (preg_match('/<ImportAndPublishInvResult[^>]*>(.*?)<\/ImportAndPublishInvResult>/si', $raw, $m)) {
            $resultStr = trim($m[1]);
        }

        // Check for SOAP Fault
        if ($resultStr === null) {
            if (preg_match('/<faultstring[^>]*>(.*?)<\/faultstring>/si', $raw, $fm)) {
                return ['success' => false, 'message' => 'VNPT SOAP Fault: ' . strip_tags($fm[1]), 'inv_no' => null];
            }
            return ['success' => false, 'message' => 'Không tìm thấy ImportAndPublishInvResult trong phản hồi VNPT.', 'inv_no' => null];
        }

        @file_put_contents($logPath, "=== ResultStr ===\n{$resultStr}\n", FILE_APPEND);

        // -----------------------------------------------------------------------
        // VNPT returns PLAIN STRING — not XML. Two formats:
        //   ERR:X  → error
        //   OK:pattern;serial-key1_num1, key2_num2, ...  → success
        // -----------------------------------------------------------------------

        // Decode any HTML entities first (SOAP may encode < > &)
        $result = html_entity_decode($resultStr, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        @file_put_contents($logPath, "=== Parsed result ===\n{$result}\n", FILE_APPEND);

        // --- Handle ERR: prefix ---
        if (stripos($result, 'ERR:') === 0) {
            $errCode = strtoupper(trim($result));
            $errMessages = [
                'ERR:1'  => 'Tài khoản đăng nhập sai hoặc không có quyền (ERR:1). Kiểm tra C_USER_ID / C_PASSWORD_ID trong cấu hình.',
                'ERR:3'  => 'Dữ liệu XML hóa đơn không đúng quy định VNPT (ERR:3). Kiểm tra cấu trúc XML.',
                'ERR:5'  => 'Không phát hành được hóa đơn — DB rollback (ERR:5).',
                'ERR:7'  => 'Username không phù hợp, không tìm thấy công ty tương ứng (ERR:7). Kiểm tra WS_USER_ID.',
                'ERR:10' => 'Số hóa đơn vượt quá giới hạn tối đa cho phép (ERR:10).',
                'ERR:20' => 'Pattern và Serial không phù hợp hoặc không tồn tại (ERR:20). Kiểm tra PATTERN_HD_ID / SERIAL_HD_ID.',
            ];
            $msg = $errMessages[$errCode] ?? "VNPT lỗi: {$result}";
            return ['success' => false, 'message' => $msg, 'inv_no' => null];
        }

        // --- Handle OK: prefix ---
        // Format: OK:pattern;serial-key1_num1, key2_num2, ...
        if (stripos($result, 'OK:') === 0) {
            $okBody = substr($result, 3); // strip "OK:"
            // Extract invoice number: after the last "_" in first key-num pair
            // Example: OK:1/003;C23TAA-BL-202601-HD00001-ABCD_1, key2_2
            $invNo = '';
            if (preg_match('/[^_]+_(\d+)/', $okBody, $nm)) {
                $invNo = $nm[1];
            }
            // Also try to get pattern and serial
            $pattern = '';
            $serial  = '';
            if (preg_match('/^([^;]+);([^-]+)-/', $okBody, $ps)) {
                $pattern = trim($ps[1]);
                $serial  = trim($ps[2]);
            }
            $displayNo = $invNo ?: $okBody;
            @file_put_contents($logPath, "=== SUCCESS inv_no={$invNo} pattern={$pattern} serial={$serial} ===\n", FILE_APPEND);
            return ['success' => true, 'message' => 'OK', 'inv_no' => $displayNo, 'raw_ok' => $okBody];
        }

        // --- Fallback: unknown format ---
        $preview = mb_substr($result, 0, 300);
        return ['success' => false, 'message' => "Phản hồi VNPT không nhận dạng được: {$preview}", 'inv_no' => null];
    }

    /**
     * Extract ERR_CODE / INV_NO from a parsed SimpleXML result node.
     */
    protected function extractFromResultXml(\SimpleXMLElement $xml): array
    {
        $errCode = (string)($xml->ERR_CODE ?? $xml->errorCode ?? '');
        $errMsg  = (string)($xml->ERR_MSG ?? $xml->description ?? '');
        $invNo   = (string)($xml->INV_NO ?? $xml->invoiceNo ?? '');
        $guid    = (string)($xml->INVOICE_GUID ?? $xml->guid ?? '');

        if ($errCode === '') {
            foreach (['Inv', 'Invoice', 'RESULT', 'result'] as $tag) {
                $child = $xml->{$tag} ?? null;
                if ($child) {
                    $errCode = (string)($child->ERR_CODE ?? '');
                    $errMsg  = (string)($child->ERR_MSG ?? '');
                    $invNo   = (string)($child->INV_NO ?? '');
                    $guid    = (string)($child->INVOICE_GUID ?? '');
                    if ($errCode !== '') break;
                }
            }
        }

        if ($errCode === '0' || $guid !== '' || ($invNo !== '' && $errCode !== '1')) {
            return ['success' => true, 'message' => 'OK', 'inv_no' => $invNo ?: $guid];
        }

        return [
            'success' => false,
            'message' => "VNPT lỗi [{$errCode}]: " . ($errMsg ?: 'Xem file writable/logs/vnpt_debug.log'),
            'inv_no'  => null,
        ];
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Convert a Vietnamese amount number to words.
     */
    public function numberToWords(int $amount): string
    {
        if ($amount === 0) return 'Không đồng';

        $ones = ['', 'một', 'hai', 'ba', 'bốn', 'năm', 'sáu', 'bảy', 'tám', 'chín'];
        $tens = ['', 'mười', 'hai mươi', 'ba mươi', 'bốn mươi', 'năm mươi',
                 'sáu mươi', 'bảy mươi', 'tám mươi', 'chín mươi'];

        $readGroup = function(int $n) use ($ones, $tens): string {
            $h = intdiv($n, 100);
            $t = intdiv($n % 100, 10);
            $o = $n % 10;
            $s = '';
            if ($h > 0) $s .= $ones[$h] . ' trăm ';
            if ($t > 1)  $s .= $tens[$t] . ' ';
            elseif ($t === 1) $s .= 'mười ';
            elseif ($t === 0 && $h > 0 && $o > 0) $s .= 'linh ';
            if ($o > 0) $s .= ($o === 1 && $t > 1 ? 'mốt' : ($o === 5 && $t > 0 ? 'lăm' : $ones[$o]));
            return trim($s);
        };

        $billions  = intdiv($amount, 1_000_000_000);
        $millions  = intdiv($amount % 1_000_000_000, 1_000_000);
        $thousands = intdiv($amount % 1_000_000, 1_000);
        $remainder = $amount % 1_000;

        $result = '';
        if ($billions)  $result .= $readGroup($billions) . ' tỷ ';
        if ($millions)  $result .= $readGroup($millions) . ' triệu ';
        if ($thousands) $result .= $readGroup($thousands) . ' nghìn ';
        if ($remainder) $result .= $readGroup($remainder);

        return ucfirst(trim($result)) . ' đồng';
    }
}
