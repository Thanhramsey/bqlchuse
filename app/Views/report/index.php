<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Báo cáo thống kê<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<ol class="breadcrumb breadcrumb-arrows" aria-label="breadcrumbs">
    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Trang chủ</a></li>
    <li class="breadcrumb-item active" aria-current="page"><a href="#">Báo cáo</a></li>
</ol>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row row-cards">
    <!-- Filter Card -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <form id="report-filter-form" class="row g-3 align-items-end">
                    <div class="col-md-3 col-6">
                        <label class="form-label font-weight-bold">Từ tháng</label>
                        <input type="month" id="filter-from" class="form-control" placeholder="Chọn tháng bắt đầu">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label font-weight-bold">Đến tháng</label>
                        <input type="month" id="filter-to" class="form-control" placeholder="Chọn tháng kết thúc">
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label font-weight-bold">Tuyến thu gom</label>
                        <select id="filter-route" class="form-select">
                            <option value="">-- Tất cả các tuyến --</option>
                            <?php foreach ($routes as $r) : ?>
                                <option value="<?= $r['id'] ?>"><?= esc($r['route_code']) ?> - <?= esc($r['route_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 col-6">
                        <label class="form-label font-weight-bold">Nhân viên thu ngân</label>
                        <select id="filter-collector" class="form-select">
                            <option value="">-- Tất cả nhân viên --</option>
                            <?php foreach ($collectors as $c) : ?>
                                <option value="<?= $c['id'] ?>"><?= esc($c['fullname']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button type="button" id="btn-stats" class="btn btn-primary">
                            <i class="ti ti-chart-bar me-1"></i>Thống kê
                        </button>
                        <a href="#" id="btn-export-excel" class="btn btn-success">
                            <i class="ti ti-file-spreadsheet me-1"></i>Xuất Excel
                        </a>
                        <a href="#" id="btn-export-pdf" target="_blank" class="btn btn-danger">
                            <i class="ti ti-printer me-1"></i>In báo cáo
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- KPI Summaries -->
    <div class="col-md-6">
        <div class="card card-sm bg-success-lt" style="border-left: 4px solid #2fb344;">
            <div class="card-body d-flex align-items-center">
                <span class="bg-success text-white stamp me-3" style="width: 2.5rem; height: 2.5rem; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                    <i class="ti ti-wallet fs-2"></i>
                </span>
                <div>
                    <h3 class="lh-1 mb-1 font-weight-bold text-success" id="kpi-collected-amount">0 VNĐ</h3>
                    <div class="text-secondary" id="kpi-collected-count">Tổng thu: 0 phiếu thu</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-sm bg-teal-lt" style="border-left: 4px solid #0ca678;">
            <div class="card-body d-flex align-items-center">
                <span class="bg-teal text-white stamp me-3" style="width: 2.5rem; height: 2.5rem; display: flex; align-items: center; justify-content: center; border-radius: 4px;">
                    <i class="ti ti-file-check fs-2"></i>
                </span>
                <div>
                    <h3 class="lh-1 mb-1 font-weight-bold text-teal" id="kpi-invoiced-amount">0 VNĐ</h3>
                    <div class="text-secondary" id="kpi-invoiced-count">Tổng xuất hóa đơn: 0 hóa đơn</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Report Tables -->
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <ul class="nav nav-tabs" data-bs-toggle="tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="#tab-route" class="nav-link active" data-bs-toggle="tab" role="tab" aria-selected="true">
                            <i class="ti ti-map-2 me-1"></i>Theo tuyến thu gom
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#tab-user" class="nav-link" data-bs-toggle="tab" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ti ti-user-check me-1"></i>Theo nhân viên thu
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#tab-detail" class="nav-link" data-bs-toggle="tab" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ti ti-list-details me-1"></i>Báo cáo chi tiết
                        </a>
                    </li>
                </ul>

                <div class="tab-content pt-3">
                    <!-- Tab Route -->
                    <div class="tab-pane active show" id="tab-route" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-vcenter table-striped table-hover card-table">
                                <thead>
                                    <tr>
                                        <th>Mã tuyến</th>
                                        <th>Tên tuyến</th>
                                        <th>Số phiếu đã thu</th>
                                        <th>Tổng tiền thu (VND)</th>
                                        <th>Số HĐ đã xuất</th>
                                        <th>Tổng tiền xuất HĐ (VND)</th>
                                    </tr>
                                </thead>
                                <tbody id="body-route">
                                    <tr><td colspan="6" class="text-center py-3 text-secondary">Đang tải dữ liệu...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab User -->
                    <div class="tab-pane" id="tab-user" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-vcenter table-striped table-hover card-table">
                                <thead>
                                    <tr>
                                        <th>Tài khoản</th>
                                        <th>Họ tên nhân viên</th>
                                        <th>Số phiếu đã thu</th>
                                        <th>Tổng tiền thu (VND)</th>
                                        <th>Số HĐ đã xuất</th>
                                        <th>Tổng tiền xuất HĐ (VND)</th>
                                    </tr>
                                </thead>
                                <tbody id="body-user">
                                    <tr><td colspan="6" class="text-center py-3 text-secondary">Đang tải dữ liệu...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Detail -->
                    <div class="tab-pane" id="tab-detail" role="tabpanel">
                        <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                            <table class="table table-vcenter table-striped table-hover card-table">
                                <thead>
                                    <tr>
                                        <th>Mã hộ</th>
                                        <th>Họ tên chủ hộ</th>
                                        <th>Địa chỉ</th>
                                        <th>Tuyến thu gom</th>
                                        <th>Kỳ nộp</th>
                                        <th>Số tiền (VND)</th>
                                        <th>Trạng thái</th>
                                        <th>Số phiếu thu</th>
                                        <th>Số HĐ VNPT</th>
                                        <th>Nhân viên thu</th>
                                        <th>Ngày thu</th>
                                    </tr>
                                </thead>
                                <tbody id="body-detail">
                                    <tr><td colspan="11" class="text-center py-3 text-secondary">Đang tải dữ liệu...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        // Set default month inputs to current year Jan - current month
        const d = new Date();
        const currYear = d.getFullYear();
        const currMonth = String(d.getMonth() + 1).padStart(2, '0');
        
        $('#filter-from').val(`${currYear}-01`);
        $('#filter-to').val(`${currYear}-${currMonth}`);

        loadReports();

        $('#btn-stats').on('click', function() {
            loadReports();
        });
    });

    function loadReports() {
        const fromVal = $('#filter-from').val();
        const toVal = $('#filter-to').val();
        const routeVal = $('#filter-route').val();
        const collectorVal = $('#filter-collector').val();

        // Update Excel / PDF Export Links
        $('#btn-export-excel').attr('href', `<?= base_url('reports/export/excel') ?>?from_month=${fromVal}&to_month=${toVal}&route_id=${routeVal}&collector_id=${collectorVal}`);
        $('#btn-export-pdf').attr('href', `<?= base_url('reports/export/pdf') ?>?from_month=${fromVal}&to_month=${toVal}&route_id=${routeVal}&collector_id=${collectorVal}`);

        $('#body-route, #body-user, #body-detail').html('<tr><td colspan="11" class="text-center py-3 text-secondary"><div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>Đang tải dữ liệu...</td></tr>');

        $.ajax({
            url: '<?= base_url('reports/revenue') ?>',
            method: 'GET',
            data: {
                from_month: fromVal,
                to_month: toVal,
                route_id: routeVal,
                collector_id: collectorVal
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    renderReports(res.data);
                } else {
                    Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Không thể tải dữ liệu báo cáo.' });
                }
            },
            error: function() {
                Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Lỗi kết nối máy chủ.' });
            }
        });
    }

    function renderReports(data) {
        // 1. Calculate KPI values from detailed transaction records to respect route/collector filters
        let kpiCollectedAmt = 0;
        let kpiCollectedCount = 0;
        let kpiInvoicedAmt = 0;
        let kpiInvoicedCount = 0;

        data.details.forEach(row => {
            const amount = parseFloat(row.amount) || 0;
            kpiCollectedAmt += amount;
            kpiCollectedCount++;

            if (row.payment_status === 'Đã xuất hóa đơn') {
                kpiInvoicedAmt += amount;
                kpiInvoicedCount++;
            }
        });

        // 2. Render Route Table & Calculate its own Totals
        let totalRouteCollectedAmt = 0;
        let totalRouteCollectedCount = 0;
        let totalRouteInvoicedAmt = 0;
        let totalRouteInvoicedCount = 0;

        let htmlRoute = '';
        if (data.by_route.length === 0) {
            htmlRoute = '<tr><td colspan="6" class="text-center text-muted py-3">Không tìm thấy dữ liệu doanh thu theo tuyến trong khoảng thời gian này.</td></tr>';
        } else {
            data.by_route.forEach(row => {
                const colAmt = parseFloat(row.total_amount) || 0;
                const colCount = parseInt(row.total_count) || 0;
                const invAmt = parseFloat(row.invoiced_amount) || 0;
                const invCount = parseInt(row.invoiced_count) || 0;

                htmlRoute += `
                    <tr>
                        <td><code>${esc(row.route_code)}</code></td>
                        <td><strong>${esc(row.group_key)}</strong></td>
                        <td>${colCount}</td>
                        <td class="text-blue font-weight-medium">${format_money(colAmt)}</td>
                        <td>${invCount}</td>
                        <td class="text-teal font-weight-medium">${format_money(invAmt)}</td>
                    </tr>
                `;

                totalRouteCollectedAmt += colAmt;
                totalRouteCollectedCount += colCount;
                totalRouteInvoicedAmt += invAmt;
                totalRouteInvoicedCount += invCount;
            });

            htmlRoute += `
                <tr class="font-weight-bold bg-light">
                    <td colspan="2">Tổng cộng</td>
                    <td>${totalRouteCollectedCount}</td>
                    <td class="text-blue">${format_money(totalRouteCollectedAmt)}</td>
                    <td>${totalRouteInvoicedCount}</td>
                    <td class="text-teal">${format_money(totalRouteInvoicedAmt)}</td>
                </tr>
            `;
        }
        $('#body-route').html(htmlRoute);

        // 3. Render User Table & Calculate its own Totals
        let totalUserCollectedAmt = 0;
        let totalUserCollectedCount = 0;
        let totalUserInvoicedAmt = 0;
        let totalUserInvoicedCount = 0;

        let htmlUser = '';
        if (data.by_collector.length === 0) {
            htmlUser = '<tr><td colspan="6" class="text-center text-muted py-3">Không tìm thấy dữ liệu doanh thu theo nhân viên trong khoảng thời gian này.</td></tr>';
        } else {
            data.by_collector.forEach(row => {
                const colAmt = parseFloat(row.total_amount) || 0;
                const colCount = parseInt(row.total_count) || 0;
                const invAmt = parseFloat(row.invoiced_amount) || 0;
                const invCount = parseInt(row.invoiced_count) || 0;

                htmlUser += `
                    <tr>
                        <td><code>${esc(row.username)}</code></td>
                        <td><strong>${esc(row.group_key)}</strong></td>
                        <td>${colCount}</td>
                        <td class="text-blue font-weight-medium">${format_money(colAmt)}</td>
                        <td>${invCount}</td>
                        <td class="text-teal font-weight-medium">${format_money(invAmt)}</td>
                    </tr>
                `;

                totalUserCollectedAmt += colAmt;
                totalUserCollectedCount += colCount;
                totalUserInvoicedAmt += invAmt;
                totalUserInvoicedCount += invCount;
            });

            htmlUser += `
                <tr class="font-weight-bold bg-light">
                    <td colspan="2">Tổng cộng</td>
                    <td>${totalUserCollectedCount}</td>
                    <td class="text-blue">${format_money(totalUserCollectedAmt)}</td>
                    <td>${totalUserInvoicedCount}</td>
                    <td class="text-teal">${format_money(totalUserInvoicedAmt)}</td>
                </tr>
            `;
        }
        $('#body-user').html(htmlUser);

        // 4. Render Details Table & Calculate its own Totals
        let htmlDetail = '';
        let totalDetailAmt = 0;
        if (data.details.length === 0) {
            htmlDetail = '<tr><td colspan="11" class="text-center text-muted py-3">Không có giao dịch chi tiết nào thỏa mãn bộ lọc.</td></tr>';
        } else {
            data.details.forEach(row => {
                const amount = parseFloat(row.amount) || 0;
                const dateStr = format_date(row.payment_date);
                const statusBadge = row.payment_status === 'Đã xuất hóa đơn' 
                    ? '<span class="badge bg-success-lt">Đã xuất hóa đơn</span>' 
                    : '<span class="badge bg-warning-lt">Đã thu tiền</span>';
                
                const formattedReceipt = row.receipt_code ? esc(row.receipt_code) : '<span class="text-muted">—</span>';
                const formattedInvNo = row.vnpt_inv_no ? `<span class="badge bg-teal-lt text-teal">HĐ: ${esc(row.vnpt_inv_no)}</span>` : '<span class="text-muted">—</span>';

                htmlDetail += `
                    <tr>
                        <td><span class="text-secondary">${esc(row.household_code)}</span></td>
                        <td><strong>${esc(row.owner_name)}</strong></td>
                        <td class="text-truncate" style="max-width: 150px;" title="${esc(row.address)}">${esc(row.address)}</td>
                        <td>${esc(row.route_name)}</td>
                        <td><code>${esc(row.billing_month)}</code></td>
                        <td class="text-blue font-weight-medium">${format_money(amount)}</td>
                        <td>${statusBadge}</td>
                        <td><code>${formattedReceipt}</code></td>
                        <td>${formattedInvNo}</td>
                        <td>${esc(row.collector_name || 'Thu ngân')}</td>
                        <td class="small">${dateStr}</td>
                    </tr>
                `;
                totalDetailAmt += amount;
            });

            htmlDetail += `
                <tr class="font-weight-bold bg-light">
                    <td colspan="5">Tổng cộng</td>
                    <td class="text-blue">${format_money(totalDetailAmt)}</td>
                    <td colspan="5"></td>
                </tr>
            `;
        }
        $('#body-detail').html(htmlDetail);

        // 5. Update KPI Dashboard using calculated KPI values
        $('#kpi-collected-amount').text(format_money(kpiCollectedAmt));
        $('#kpi-collected-count').text(`Tổng thu: ${kpiCollectedCount} phiếu thu`);
        
        $('#kpi-invoiced-amount').text(format_money(kpiInvoicedAmt));
        $('#kpi-invoiced-count').text(`Tổng xuất hóa đơn: ${kpiInvoicedCount} hóa đơn`);
    }

    function format_money(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }

    function format_date(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('vi-VN') + ' ' + d.toLocaleTimeString('vi-VN', {hour: '2-digit', minute:'2-digit'});
    }

    function esc(string) {
        if (!string) return '';
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return string.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
</script>
<?= $this->endSection() ?>
