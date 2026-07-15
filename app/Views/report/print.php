<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo doanh thu thu phí vệ sinh môi trường</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            color: #333;
            line-height: 1.6;
            margin: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 5px 0;
        }
        .section-title {
            margin-top: 30px;
            margin-bottom: 10px;
            border-bottom: 1px solid #777;
            padding-bottom: 5px;
            color: #111;
            font-size: 16px;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th, table td {
            border: 1px solid #333;
            padding: 8px;
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
            margin-top: 50px;
            text-align: right;
            font-style: italic;
        }
        @media print {
            .no-print {
                display: none;
            }
        }
        .btn-print {
            background-color: #d63939;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            cursor: pointer;
            border-radius: 4px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center;">
        <button class="btn-print" onclick="window.print();">In Báo Cáo</button>
    </div>

    <div class="header">
        <h3>ỦY BAN NHÂN DÂN QUẬN HAI BÀ TRƯNG</h3>
        <h4>BAN QUẢN LÝ VỆ SINH MÔ TRƯỜNG PHƯỜNG NGUYỄN DU</h4>
        <h1>BÁO CÁO DOANH THU THU PHÍ RÁC THẢI</h1>
        <p>Ngày xuất báo cáo: <?= date('d/m/Y H:i:s') ?></p>
    </div>

    <!-- Section 1 -->
    <div class="section-title">1. DOANH THU THEO THÁNG</div>
    <table>
        <thead>
            <tr>
                <th>Kỳ thu phí (Tháng)</th>
                <th class="center">Số hóa đơn đã thu</th>
                <th class="right">Tổng tiền thu được (VND)</th>
            </tr>
        </thead>
        <tbody>
            <?php $total = 0; foreach ($by_month as $row) : $total += $row['total_amount']; ?>
                <tr>
                    <td><strong><?= esc($row['group_key']) ?></strong></td>
                    <td class="center"><?= esc($row['bills_count']) ?></td>
                    <td class="right"><?= number_format((double)$row['total_amount'], 0, ',', '.') ?> VNĐ</td>
                </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold; background-color: #f9f9f9;">
                <td colspan="2" class="right">Tổng cộng:</td>
                <td class="right" style="color: #d63939;"><?= number_format($total, 0, ',', '.') ?> VNĐ</td>
            </tr>
        </tbody>
    </table>

    <!-- Section 2 -->
    <div class="section-title">2. DOANH THU THEO PHÂN LOẠI HỘ</div>
    <table>
        <thead>
            <tr>
                <th>Phân loại hộ dân</th>
                <th class="center">Số hóa đơn đã thu</th>
                <th class="right">Tổng tiền thu được (VND)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($by_household_type as $row) : ?>
                <tr>
                    <td><strong><?= esc($row['group_key']) ?></strong></td>
                    <td class="center"><?= esc($row['bills_count']) ?></td>
                    <td class="right"><?= number_format((double)$row['total_amount'], 0, ',', '.') ?> VNĐ</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Section 3 -->
    <div class="section-title">3. DOANH THU THEO TỔ DÂN PHỐ</div>
    <table>
        <thead>
            <tr>
                <th>Tổ dân phố / Khu phố</th>
                <th class="center">Số hóa đơn đã thu</th>
                <th class="right">Tổng tiền thu được (VND)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($by_ward_group as $row) : ?>
                <tr>
                    <td><strong><?= esc($row['group_key']) ?></strong></td>
                    <td class="center"><?= esc($row['bills_count']) ?></td>
                    <td class="right"><?= number_format((double)$row['total_amount'], 0, ',', '.') ?> VNĐ</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <!-- Section 4 -->
    <div class="section-title">4. HIỆU SUẤT THU THEO NHÂN VIÊN THU NGÂN</div>
    <table>
        <thead>
            <tr>
                <th>Nhân viên thu ngân</th>
                <th class="center">Số hóa đơn đã thu</th>
                <th class="right">Tổng tiền thu được (VND)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($by_collector as $row) : ?>
                <tr>
                    <td><strong><?= esc($row['group_key']) ?></strong></td>
                    <td class="center"><?= esc($row['bills_count']) ?></td>
                    <td class="right"><?= number_format((double)$row['total_amount'], 0, ',', '.') ?> VNĐ</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="footer-print">
        <p>Hà Nội, Ngày ..... tháng ..... năm 2026</p>
        <strong style="margin-right: 50px;">Người lập báo cáo</strong>
        <div style="height: 80px;"></div>
        <strong style="margin-right: 35px;"><?= esc(session('fullname')) ?></strong>
    </div>

    <script>
        window.onload = function() {
            // Automatically open printer setup dialog after brief delay
            setTimeout(function() {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
