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
        $fkey   = $payment['receipt_code'] ?: ('QLRAC-' . $paymentId . '-' . time());
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
    // XML Builder — VNPT standard invoice XML format
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
        $db    = \Config\Database::connect();
        $rate  = $db->table('fee_rates')
            ->where('id', $payment['fee_rate_id'] ?? 0)
            ->get()->getRowArray();

        $price     = $rate ? (float)$rate['price'] : (float)($payment['amount'] / ($monthsCount ?: 1) / 1.1);
        $vatRate   = $rate ? (float)$rate['vat'] : 10.0;
        $subtotal  = $price * $monthsCount;
        $vatAmt    = round($subtotal * $vatRate / 100, 0);
        $total     = $subtotal + $vatAmt;

        $prodName  = "Phí vệ sinh môi trường tháng " . str_pad($fromM, 2, '0', STR_PAD_LEFT) . '/' . $fromYear;
        if ($fromM !== $toM || $fromYear !== $toYear) {
            $prodName .= ' - ' . str_pad($toM, 2, '0', STR_PAD_LEFT) . '/' . $toYear;
        }

        $cusName    = htmlspecialchars($household['owner_name'] ?? '', ENT_XML1, 'UTF-8');
        $cusAddress = htmlspecialchars($household['address'] ?? '', ENT_XML1, 'UTF-8');
        $cusCode    = htmlspecialchars($household['household_code'] ?? '', ENT_XML1, 'UTF-8');
        $prodNameE  = htmlspecialchars($prodName, ENT_XML1, 'UTF-8');
        $fkeyE      = htmlspecialchars($fkey, ENT_XML1, 'UTF-8');
        $amountWords = $this->numberToWords((int)$total);
        $invDate    = date('Y-m-d', strtotime($payment['payment_date'] ?? 'now'));

        $xml = <<<XML
<Invoices>
  <Inv>
    <Invoice>
      <CusCode>{$cusCode}</CusCode>
      <CusName>{$cusName}</CusName>
      <CusAddress>{$cusAddress}</CusAddress>
      <CusTaxCode></CusTaxCode>
      <PaymentMethod>TM/CK</PaymentMethod>
      <KindOfService>Dịch vụ vệ sinh môi trường</KindOfService>
      <InvDate>{$invDate}</InvDate>
      <Products>
        <Product>
          <Code>PVS001</Code>
          <ProdName>{$prodNameE}</ProdName>
          <ProdUnit>tháng</ProdUnit>
          <ProdQuantity>{$monthsCount}</ProdQuantity>
          <ProdPrice>{$price}</ProdPrice>
          <Amount>{$subtotal}</Amount>
          <VATRate>{$vatRate}</VATRate>
          <VATAmount>{$vatAmt}</VATAmount>
          <Total>{$total}</Total>
        </Product>
      </Products>
      <Total>{$subtotal}</Total>
      <VATRate>{$vatRate}</VATRate>
      <VATAmount>{$vatAmt}</VATAmount>
      <AmountInWords>{$amountWords}</AmountInWords>
      <Amount>{$total}</Amount>
      <fkey>{$fkeyE}</fkey>
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

        $context  = stream_context_create($options);
        $raw      = @file_get_contents($endpoint, false, $context);

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
        $logPath = WRITEPATH . 'logs/vnpt_debug.log';
        $timestamp = date('Y-m-d H:i:s');
        @file_put_contents($logPath, "\n\n=== [{$timestamp}] VNPT RAW RESPONSE ===\n{$raw}\n", FILE_APPEND);

        if (empty(trim($raw))) {
            return ['success' => false, 'message' => 'VNPT trả về phản hồi rỗng.', 'inv_no' => null];
        }

        // --- Step 1: Extract the inner result string from SOAP envelope using regex ---
        // The result tag can be: ImportAndPublishInvResult
        $resultStr = null;

        if (preg_match('/<ImportAndPublishInvResult[^>]*>(.*?)<\/ImportAndPublishInvResult>/si', $raw, $m)) {
            $resultStr = trim($m[1]);
        }

        // Also check for SOAP Fault
        if ($resultStr === null) {
            if (preg_match('/<faultstring[^>]*>(.*?)<\/faultstring>/si', $raw, $fm)) {
                $faultMsg = strip_tags($fm[1]);
                return ['success' => false, 'message' => "VNPT SOAP Fault: {$faultMsg}", 'inv_no' => null];
            }
            return ['success' => false, 'message' => 'Không tìm thấy kết quả ImportAndPublishInvResult trong phản hồi VNPT.', 'inv_no' => null];
        }

        @file_put_contents($logPath, "=== ResultStr ===\n{$resultStr}\n", FILE_APPEND);

        // --- Step 2: resultStr may be HTML-entity-encoded XML --- 
        $decoded = html_entity_decode($resultStr, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // Also try numeric entities
        $decoded = preg_replace_callback('/&#(\d+);/', function($m) { return mb_chr((int)$m[1], 'UTF-8'); }, $decoded);

        @file_put_contents($logPath, "=== Decoded ===\n{$decoded}\n", FILE_APPEND);

        // --- Step 3: Try to parse as XML ---
        libxml_use_internal_errors(true);
        $resultXml = simplexml_load_string($decoded);

        if ($resultXml !== false) {
            return $this->extractFromResultXml($resultXml);
        }

        // --- Step 4: Maybe the result is already plain XML (not entity-encoded) ---
        $resultXml2 = simplexml_load_string($resultStr);
        if ($resultXml2 !== false) {
            return $this->extractFromResultXml($resultXml2);
        }

        // --- Step 5: Try JSON ---
        $resultJson = json_decode($decoded, true);
        if ($resultJson !== null) {
            $errCode = (string)($resultJson['ERR_CODE'] ?? $resultJson['errorCode'] ?? '-1');
            $errMsg  = (string)($resultJson['ERR_MSG'] ?? $resultJson['description'] ?? 'Lỗi không rõ');
            $invNo   = (string)($resultJson['INV_NO'] ?? $resultJson['invoiceNo'] ?? '');
            if ($errCode === '0' || $invNo !== '') {
                return ['success' => true, 'message' => 'OK', 'inv_no' => $invNo];
            }
            return ['success' => false, 'message' => "VNPT lỗi [{$errCode}]: {$errMsg}", 'inv_no' => null];
        }

        // --- Step 6: Try regex-only parse on common patterns ---
        // Pattern: ERR_CODE=0, INV_NO=123
        if (preg_match('/<ERR_CODE[^>]*>(\d+)<\/ERR_CODE>/i', $decoded, $ec)) {
            $errCode = $ec[1];
            $invNo = '';
            if (preg_match('/<INV_NO[^>]*>([^<]+)<\/INV_NO>/i', $decoded, $in)) {
                $invNo = trim($in[1]);
            }
            $errMsg = '';
            if (preg_match('/<ERR_MSG[^>]*>([^<]*)<\/ERR_MSG>/i', $decoded, $em)) {
                $errMsg = trim($em[1]);
            }
            if ($errCode === '0' || $invNo !== '') {
                return ['success' => true, 'message' => 'OK', 'inv_no' => $invNo];
            }
            return ['success' => false, 'message' => "VNPT lỗi [{$errCode}]: " . ($errMsg ?: 'Xem log vnpt_debug.log để biết thêm chi tiết'), 'inv_no' => null];
        }

        // Fallback: return the raw string as error
        $preview = mb_substr($decoded ?: $resultStr, 0, 300);
        return ['success' => false, 'message' => "Phản hồi VNPT không phân tích được. Nội dung: {$preview}", 'inv_no' => null];
    }

    /**
     * Extract ERR_CODE / INV_NO from a parsed SimpleXML result node.
     */
    protected function extractFromResultXml(\SimpleXMLElement $xml): array
    {
        // VNPT returns flat: <result><ERR_CODE>0</ERR_CODE><INV_NO>1</INV_NO>...</result>
        // or nested under <Inv>
        $errCode = (string)($xml->ERR_CODE ?? $xml->errorCode ?? '');
        $errMsg  = (string)($xml->ERR_MSG ?? $xml->description ?? '');
        $invNo   = (string)($xml->INV_NO ?? $xml->invoiceNo ?? '');
        $guid    = (string)($xml->INVOICE_GUID ?? $xml->guid ?? '');

        // Nested check
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

        // Success: ERR_CODE=0 or we got a GUID or INV_NO
        if ($errCode === '0' || $guid !== '' || ($invNo !== '' && $errCode !== '1')) {
            return ['success' => true, 'message' => 'OK', 'inv_no' => $invNo ?: $guid];
        }

        return [
            'success' => false,
            'message' => "VNPT lỗi [{$errCode}]: " . ($errMsg ?: 'Xem file writable/logs/vnpt_debug.log để biết thêm chi tiết'),
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
