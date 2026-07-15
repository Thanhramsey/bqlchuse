<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestVnpt extends BaseCommand
{
    protected $group = 'VNPT';
    protected $name = 'vnpt:test';
    protected $description = 'Test VNPT Electronic Invoice publishing using the service';
    protected $usage = 'vnpt:test [payment_id]';
    protected $arguments = ['payment_id' => 'Optional ID of payment to test'];

    public function run(array $params)
    {
        $paymentId = !empty($params) ? (int)$params[0] : 0;
        CLI::write("Testing VNPT Electronic Invoice Publishing...", "yellow");

        $vnptService = new \App\Services\VnptInvoiceService();

        if ($paymentId > 0) {
            CLI::write("Using payment ID: {$paymentId}", "cyan");
            $db = \Config\Database::connect();
            $payment = $db->table('payments')->where('id', $paymentId)->get()->getRowArray();
            if (!$payment) {
                CLI::error("Payment not found in DB!");
                return;
            }
            $household = $db->table('households')->where('id', $payment['household_id'])->get()->getRowArray();
            if (!$household) {
                CLI::error("Household not found in DB!");
                return;
            }
            $fkey = $payment['receipt_code'] ?: ('TEST-DB-' . $paymentId . '-' . time());
        } else {
            CLI::write("Using mock data...", "cyan");
            $payment = [
                'id'                 => 0,
                'billing_from_month' => '2026-01',
                'billing_to_month'   => '2026-01',
                'payment_date'       => date('Y-m-d H:i:s'),
                'amount'             => 33000,
                'fee_rate_id'        => null,
                'receipt_code'       => 'TEST-MOCK-' . time(),
                'collector_name'     => 'Admin Test',
            ];
            $household = [
                'household_code' => 'HD00001',
                'owner_name'     => 'Nguyen Van A',
                'address'        => 'So 1 Pho Test, Hanoi',
            ];
            $fkey = $payment['receipt_code'];
        }

        $xmlInv = $vnptService->buildInvXml($payment, $household, $fkey);
        CLI::write("Generated XML:", "green");
        CLI::write($xmlInv);

        CLI::write("\nCalling VNPT SOAP Service...", "yellow");
        
        // Use reflection to call the protected callPublishService method
        $ref = new \ReflectionClass($vnptService);
        $method = $ref->getMethod('callPublishService');
        $method->setAccessible(true);
        
        $soapResult = $method->invoke($vnptService, $xmlInv);
        
        CLI::write("SOAP call success status: " . ($soapResult['success'] ? 'YES' : 'NO'), $soapResult['success'] ? 'green' : 'red');
        if (!$soapResult['success']) {
            CLI::error($soapResult['message']);
            return;
        }

        CLI::write("\nRaw SOAP Response:", "yellow");
        CLI::write($soapResult['raw']);

        CLI::write("\nParsing Response...", "yellow");
        $parseMethod = $ref->getMethod('parseResponse');
        $parseMethod->setAccessible(true);
        $parsed = $parseMethod->invoke($vnptService, $soapResult['raw']);

        CLI::write("Parsed Success: " . ($parsed['success'] ? 'YES' : 'NO'), $parsed['success'] ? 'green' : 'red');
        CLI::write("Parsed Message: " . $parsed['message']);
        CLI::write("Parsed Invoice No: " . ($parsed['inv_no'] ?: 'NULL'));
    }
}
