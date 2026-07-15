<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>In Phiếu Thu / Hóa Đơn</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.5;
            margin: 20px;
            background-color: #f9f9f9;
        }
        .actions-panel {
            max-width: 650px;
            margin: 0 auto 20px auto;
            text-align: center;
        }
        .btn-print {
            background-color: #206bc4;
            color: white;
            border: none;
            padding: 10px 25px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
        }
        .btn-print:hover {
            background-color: #1a539b;
        }
        .receipt-page {
            max-width: 650px;
            margin: 0 auto 40px auto;
            border: 1px solid #ccc;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .header {
            text-align: center;
            border-bottom: 2px dashed #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .header h3 {
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        .header h4 {
            margin: 0 0 5px 0;
            font-weight: normal;
            font-size: 14px;
        }
        .header h2 {
            margin: 15px 0 5px 0;
            color: #111;
            font-size: 20px;
        }
        .header p {
            margin: 3px 0;
            font-style: italic;
            font-size: 13px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            width: 160px;
        }
        .info-value {
            flex-grow: 1;
            border-bottom: 1px dotted #999;
        }
        .table-details {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .table-details th, .table-details td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }
        .table-details th {
            background-color: #f2f2f2;
        }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 25px;
            text-align: center;
        }
        .signature-box {
            width: 45%;
        }
        .signature-space {
            height: 70px;
        }
        @media print {
            .actions-panel {
                display: none;
            }
            body {
                margin: 0;
                background-color: #fff;
            }
            .receipt-page {
                border: none;
                padding: 0;
                margin: 0 auto;
                box-shadow: none;
                page-break-after: always;
            }
            .receipt-page:last-child {
                page-break-after: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="actions-panel">
        <button class="btn-print" onclick="window.print();">In phiếu / Hóa đơn</button>
        <p style="margin-top: 5px; font-size: 12px; color: #666;">(Trang in sẽ tự động căn lề ngắt trang khi in hàng loạt)</p>
    </div>

    <?php foreach ($printData as $data) : ?>
        <div class="receipt-page">
            <div class="header">
                <h3><?= esc($config['company_name']) ?></h3>
                <h4>Điện thoại: <?= esc($config['company_phone']) ?> | Email: <?= esc($config['company_email']) ?></h4>
                <h2>
                    <?= $data['print_type'] === 'invoice' ? 'HÓA ĐƠN PHÍ DỊCH VỤ' : 'BIÊN LAI THU TIỀN PHÍ' ?>
                </h2>
                <p>(Dịch vụ thu gom và xử lý rác thải sinh hoạt)</p>
                <div style="margin-top: 10px; font-weight: bold;">Số phiếu: <?= esc($data['receipt_code'] ?: 'DỰ THẢO') ?></div>
            </div>

            <div class="info-row">
                <div class="info-label">Chủ hộ nộp tiền:</div>
                <div class="info-value"><strong><?= esc($data['household']['owner_name']) ?></strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Mã số hộ dân:</div>
                <div class="info-value"><code><?= esc($data['household']['household_code']) ?></code></div>
            </div>
            <div class="info-row">
                <div class="info-label">Địa chỉ hộ dân:</div>
                <div class="info-value"><?= esc($data['household']['address']) ?>, <?= esc($data['household']['ward_group']) ?>, Phường Nguyễn Du</div>
            </div>

            <table class="table-details">
                <thead>
                    <tr>
                        <th>Nội dung thanh toán</th>
                        <th style="text-align: center;">Kỳ áp dụng</th>
                        <th style="text-align: right;">Đơn giá tháng</th>
                        <th style="text-align: right;">Thành tiền (VND)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        $monthsCount = ($data['to_month'] - $data['from_month']) + 1;
                        $subtotal = $data['price'] * $monthsCount;
                        $taxAmount = $subtotal * ($data['vat'] / 100);
                    ?>
                    <tr>
                        <td>
                            Phí thu gom rác thải sinh hoạt (Loại hộ: <?= esc($data['household']['household_type']) ?>)<br>
                            <small style="color: #666; font-size: 11px;">(Thời gian: <?= $monthsCount ?> tháng)</small>
                        </td>
                        <td style="text-align: center; font-weight: bold;">Tháng <?= $data['from_month'] ?> - <?= $data['to_month'] ?> / <?= $data['year'] ?></td>
                        <td style="text-align: right;"><?= number_format((double)$data['price'], 0, ',', '.') ?> VNĐ</td>
                        <td style="text-align: right; font-weight: bold;"><?= number_format((double)$subtotal, 0, ',', '.') ?> VNĐ</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: normal;">Cộng tiền dịch vụ (chưa VAT):</td>
                        <td style="text-align: right;"><?= number_format((double)$subtotal, 0, ',', '.') ?> VNĐ</td>
                    </tr>
                    <tr>
                        <td colspan="3" style="text-align: right; font-weight: normal;">Thuế suất VAT (<?= (float)$data['vat'] ?>%):</td>
                        <td style="text-align: right;"><?= number_format((double)$taxAmount, 0, ',', '.') ?> VNĐ</td>
                    </tr>
                    <tr style="font-weight: bold;">
                        <td colspan="3" style="text-align: right;">Tổng thanh toán (Đã gồm VAT):</td>
                        <td style="text-align: right; color: #d63939; font-size: 15px;"><?= number_format((double)$data['total_amount'], 0, ',', '.') ?> VNĐ</td>
                    </tr>
                </tbody>
            </table>

            <div class="info-row">
                <div class="info-label" style="width: 100px;">Viết bằng chữ:</div>
                <div class="info-value" style="font-style: italic;">
                    <?php
                        // Dynamic Vietnamese numbers to words helper
                        function doc_so_chu_viet($number) {
                            $dictionary = array(
                                0                   => 'không',
                                1                   => 'một',
                                2                   => 'hai',
                                3                   => 'ba',
                                4                   => 'bốn',
                                5                   => 'năm',
                                6                   => 'sáu',
                                7                   => 'bảy',
                                8                   => 'tám',
                                9                   => 'chín',
                                10                  => 'mười',
                                11                  => 'mười một',
                                12                  => 'mười hai',
                                13                  => 'mười ba',
                                14                  => 'mười tư',
                                15                  => 'mười lăm',
                                16                  => 'mười sáu',
                                17                  => 'mười bảy',
                                18                  => 'mười tám',
                                19                  => 'mười chín',
                                20                  => 'hai mươi',
                                30                  => 'ba mươi',
                                40                  => 'bốn mươi',
                                50                  => 'năm mươi',
                                60                  => 'sáu mươi',
                                70                  => 'bảy mươi',
                                80                  => 'tám mươi',
                                90                  => 'chín mươi',
                                100                 => 'trăm',
                                1000                => 'nghìn',
                                1000000             => 'triệu',
                                1000000000          => 'tỷ'
                            );

                            if ($number < 0) return 'âm ' . doc_so_chu_viet(abs($number));
                            
                            $string = '';
                            switch (true) {
                                case $number < 21:
                                    $string = $dictionary[$number];
                                    break;
                                case $number < 100:
                                    $tens = ((int)($number / 10)) * 10;
                                    $ones = $number % 10;
                                    $string = $dictionary[$tens];
                                    if ($ones) {
                                        $string .= ' ' . ($ones == 1 ? 'mốt' : ($ones == 5 ? 'lăm' : $dictionary[$ones]));
                                    }
                                    break;
                                case $number < 1000:
                                    $hundreds = (int)($number / 100);
                                    $remainder = $number % 100;
                                    $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
                                    if ($remainder) {
                                        $string .= ' ' . ($remainder < 10 ? 'lẻ ' . doc_so_chu_viet($remainder) : doc_so_chu_viet($remainder));
                                    }
                                    break;
                                default:
                                    $baseUnit = pow(1000, floor(log($number, 1000)));
                                    $numBaseUnits = (int)($number / $baseUnit);
                                    $remainder = $number % $baseUnit;
                                    $string = doc_so_chu_viet($numBaseUnits) . ' ' . $dictionary[$baseUnit];
                                    if ($remainder) {
                                        $string .= $remainder < 100 ? ' lẻ ' . doc_so_chu_viet($remainder) : ' ' . doc_so_chu_viet($remainder);
                                    }
                                    break;
                            }
                            return $string;
                        }

                        $amt = (int)$data['total_amount'];
                        echo ucfirst(doc_so_chu_viet($amt)) . ' đồng chẵn';
                    ?>
                </div>
            </div>

            <div class="info-row">
                <div class="info-label" style="width: 100px;">Ngày thu tiền:</div>
                <div class="info-value">
                    <?= $data['payment_date'] ? date('d/m/Y H:i', strtotime($data['payment_date'])) : date('d/m/Y H:i') ?>
                </div>
            </div>

            <div class="signatures">
                <div class="signature-box">
                    <strong>Người nộp tiền</strong>
                    <p style="font-size: 11px; color: #666; margin: 3px 0;">(Ký, ghi rõ họ tên)</p>
                    <div class="signature-space"></div>
                </div>
                <div class="signature-box">
                    <strong>Người thu tiền</strong>
                    <p style="font-size: 11px; color: #666; margin: 3px 0;">(Ký, ghi rõ họ tên)</p>
                    <div class="signature-space"></div>
                    <strong><?= esc($data['collector_name'] ?: 'Thu ngân viên') ?></strong>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

    <script>
        window.onload = function() {
            // Auto open printer on load after brief delay
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
