<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo doanh thu thu phí vệ sinh môi trường</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 5px 0;
            font-size: 18px;
        }
        .header h3, .header h4 {
            margin: 2px 0;
            font-weight: normal;
        }
        .section-title {
            margin-top: 20px;
            margin-bottom: 8px;
            border-bottom: 1px solid #777;
            padding-bottom: 3px;
            color: #111;
            font-size: 13px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table th, table td {
            border: 1px solid #333;
            padding: 5px 8px;
            text-align: left;
        }
        table th {
            background-color: #f2f2f2;
        }
        .right {
            text-align: right;
        }
        .center {
            text-align: center;
        }
        .footer-print {
            margin-top: 30px;
            text-align: right;
            font-style: italic;
            font-size: 11px;
        }
        @media print {
            .no-print {
                display: none;
            }
            body {
                margin: 10px;
            }
        }
        .btn-print {
            background-color: #206bc4;
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .btn-print:hover {
            background-color: #1a539b;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center;">
        <button class="btn-print" onclick="window.print();">In Báo Cáo</button>
    </div>

    <div class="header">
        <h3>BAN QUẢN LÝ DỊCH VỤ CÔNG ÍCH PHƯỜNG/XÃ</h3>
        <h1>BÁO CÁO DOANH THU THU PHÍ RÁC THẢI SINH HOẠT</h1>
        <p style="font-weight: bold; margin: 3px 0;">
            Kỳ thống kê: từ <?= esc($from_month ?: 'đầu') ?> đến <?= esc($to_month ?: 'nay') ?>
            <?php if (!empty($selected_route)) : ?> | Tuyến: <?= esc($selected_route) ?><?php endif; ?>
            <?php if (!empty($selected_collector)) : ?> | Nhân viên: <?= esc($selected_collector) ?><?php endif; ?>
        </p>
        <p>Ngày xuất báo cáo: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <!-- Section 1 -->
    <div class="section-title">1. DOANH THU THEO TUYẾN THU GOM</div>
    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Mã tuyến</th>
                <th>Tên tuyến thu gom</th>
                <th class="center" style="width: 15%;">Số phiếu đã thu</th>
                <th class="right" style="width: 18%;">Tổng tiền thu (VND)</th>
                <th class="center" style="width: 15%;">Số HĐ đã xuất</th>
                <th class="right" style="width: 18%;">Tiền xuất HĐ (VND)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totCollectedAmt = 0; 
            $totCollectedCnt = 0; 
            $totInvoicedAmt = 0; 
            $totInvoicedCnt = 0; 
            foreach ($by_route as $row) : 
                $totCollectedAmt += (double)($row['total_amount'] ?? 0);
                $totCollectedCnt += (int)($row['total_count'] ?? 0);
                $totInvoicedAmt += (double)($row['invoiced_amount'] ?? 0);
                $totInvoicedCnt += (int)($row['invoiced_count'] ?? 0);
            ?>
                <tr>
                    <td><code><?= esc($row['route_code'] ?? '') ?></code></td>
                    <td><strong><?= esc($row['group_key'] ?? '') ?></strong></td>
                    <td class="center"><?= esc($row['total_count'] ?? 0) ?></td>
                    <td class="right"><?= number_format((double)($row['total_amount'] ?? 0), 0, ',', '.') ?></td>
                    <td class="center"><?= esc($row['invoiced_count'] ?? 0) ?></td>
                    <td class="right"><?= number_format((double)($row['invoiced_amount'] ?? 0), 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td colspan="2" class="center">Tổng cộng</td>
                <td class="center"><?= $totCollectedCnt ?></td>
                <td class="right"><?= number_format($totCollectedAmt, 0, ',', '.') ?></td>
                <td class="center"><?= $totInvoicedCnt ?></td>
                <td class="right"><?= number_format($totInvoicedAmt, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Section 2 -->
    <div class="section-title">2. DOANH THU THEO NHÂN VIÊN THU NGÂN</div>
    <table>
        <thead>
            <tr>
                <th style="width: 20%;">Tài khoản</th>
                <th>Họ tên nhân viên</th>
                <th class="center" style="width: 15%;">Số phiếu đã thu</th>
                <th class="right" style="width: 18%;">Tổng tiền thu (VND)</th>
                <th class="center" style="width: 15%;">Số HĐ đã xuất</th>
                <th class="right" style="width: 18%;">Tiền xuất HĐ (VND)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $totCollectedAmt2 = 0; 
            $totCollectedCnt2 = 0; 
            $totInvoicedAmt2 = 0; 
            $totInvoicedCnt2 = 0; 
            foreach ($by_collector as $row) : 
                $totCollectedAmt2 += (double)($row['total_amount'] ?? 0);
                $totCollectedCnt2 += (int)($row['total_count'] ?? 0);
                $totInvoicedAmt2 += (double)($row['invoiced_amount'] ?? 0);
                $totInvoicedCnt2 += (int)($row['invoiced_count'] ?? 0);
            ?>
                <tr>
                    <td><code><?= esc($row['username'] ?? '') ?></code></td>
                    <td><strong><?= esc($row['group_key'] ?? '') ?></strong></td>
                    <td class="center"><?= esc($row['total_count'] ?? 0) ?></td>
                    <td class="right"><?= number_format((double)($row['total_amount'] ?? 0), 0, ',', '.') ?></td>
                    <td class="center"><?= esc($row['invoiced_count'] ?? 0) ?></td>
                    <td class="right"><?= number_format((double)($row['invoiced_amount'] ?? 0), 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td colspan="2" class="center">Tổng cộng</td>
                <td class="center"><?= $totCollectedCnt2 ?></td>
                <td class="right"><?= number_format($totCollectedAmt2, 0, ',', '.') ?></td>
                <td class="center"><?= $totInvoicedCnt2 ?></td>
                <td class="right"><?= number_format($totInvoicedAmt2, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Section 3 -->
    <div class="section-title">3. CHI TIẾT CÁC GIAO DỊCH THU PHÍ</div>
    <table>
        <thead>
            <tr>
                <th>Mã hộ</th>
                <th>Chủ hộ</th>
                <th>Địa chỉ</th>
                <th>Tuyến thu gom</th>
                <th>Kỳ nộp</th>
                <th class="right">Số tiền</th>
                <th>Trạng thái</th>
                <th>Số phiếu</th>
                <th>Số HĐ VNPT</th>
                <th>Thu ngân</th>
                <th>Ngày thu</th>
            </tr>
        </thead>
        <tbody>
            <?php $totAmt3 = 0; foreach ($details as $row) : $totAmt3 += (double)($row['amount'] ?? 0); ?>
                <tr>
                    <td><?= esc($row['household_code'] ?? '') ?></td>
                    <td><strong><?= esc($row['owner_name'] ?? '') ?></strong></td>
                    <td style="font-size:10px;"><?= esc($row['address'] ?? '') ?></td>
                    <td><?= esc($row['route_name'] ?? '') ?></td>
                    <td><code><?= esc($row['billing_month'] ?? '') ?></code></td>
                    <td class="right"><?= number_format((double)($row['amount'] ?? 0), 0, ',', '.') ?></td>
                    <td><?= esc($row['payment_status'] ?? '') ?></td>
                    <td><code><?= esc($row['receipt_code'] ?? '') ?></code></td>
                    <td><?= esc($row['vnpt_inv_no'] ?? '—') ?></td>
                    <td><?= esc($row['collector_name'] ?? '—') ?></td>
                    <td style="font-size:10px;"><?= date('d/m/Y H:i', strtotime($row['payment_date'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold; background-color: #f2f2f2;">
                <td colspan="5" class="center">Tổng cộng chi tiết</td>
                <td class="right"><?= number_format($totAmt3, 0, ',', '.') ?></td>
                <td colspan="5"></td>
            </tr>
        </tbody>
    </table>

    <div class="footer-print">
        <p>Ngày xuất biểu: <?= date('d/m/Y') ?></p>
        <strong style="margin-right: 50px;">Người lập báo cáo</strong>
        <div style="height: 50px;"></div>
        <strong style="margin-right: 35px;"><?= esc(session('fullname')) ?></strong>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
