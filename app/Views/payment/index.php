<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Thu phí & Phát hành hóa đơn<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<ol class="breadcrumb breadcrumb-arrows" aria-label="breadcrumbs">
    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Trang chủ</a></li>
    <li class="breadcrumb-item active" aria-current="page"><a href="#">Thu phí</a></li>
</ol>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Hidden form for bulk printing -->
<form id="bulkPrintForm" method="POST" action="<?= base_url('payments/bulk-print') ?>" target="_blank" style="display:none;">
    <?= csrf_field() ?>
    <input type="hidden" name="year" id="print_year">
    <input type="hidden" name="from_month" id="print_from_month">
    <input type="hidden" name="to_month" id="print_to_month">
    <input type="hidden" name="print_type" id="print_type">
    <div id="print_households_container"></div>
</form>

<div class="row row-cards">
    <!-- Filter Card & Actions -->
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <h3 class="card-title">Hệ thống quản lý thu phí dịch vụ môi trường</h3>
                
                <!-- Bulk Actions (Hidden per user request) -->
                <div class="btn-list d-none">
                    <?php if (has_permission('payments.create')) : ?>
                    <button class="btn btn-success btn-bulk-action" data-action="pay">
                        <i class="ti ti-cash me-2"></i>Thu tiền hàng loạt
                    </button>
                    <button class="btn btn-info btn-bulk-action" data-action="invoice">
                        <i class="ti ti-receipt me-2"></i>Xuất hóa đơn hàng loạt
                    </button>
                    <?php endif; ?>
                    <button class="btn btn-warning btn-bulk-print" data-type="receipt">
                        <i class="ti ti-printer me-2"></i>In phiếu thu
                    </button>
                    <button class="btn btn-danger btn-bulk-print" data-type="invoice">
                        <i class="ti ti-file-invoice me-2"></i>In hóa đơn
                    </button>
                </div>
            </div>

            <!-- Filters Area -->
            <div class="card-body border-bottom py-3">
                <div class="row g-2">
                    <div class="col-md-4">
                        <div class="input-icon">
                            <span class="input-icon-addon"><i class="ti ti-search"></i></span>
                            <input type="text" id="search-input" class="form-control" placeholder="Tìm kiếm theo mã hộ, tên chủ hộ, địa chỉ...">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select id="route-select" class="form-select">
                            <option value="">-- Tất cả tuyến đường / tổ dân phố --</option>
                            <?php foreach ($routes as $r) : ?>
                                <option value="<?= $r['id'] ?>"><?= esc($r['route_code']) ?> - <?= esc($r['route_name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select id="status-select" class="form-select">
                            <option value="">-- Tất cả trạng thái gần nhất --</option>
                            <option value="Chưa thu tiền">Chưa thu tiền</option>
                            <option value="Đã thu tiền">Đã thu tiền</option>
                            <option value="Đã xuất hóa đơn">Đã xuất hóa đơn</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Grid Table -->
            <div class="table-responsive p-3">
                <table id="householdsBillingTable" class="table table-striped table-hover card-table table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th class="w-1"><input type="checkbox" class="form-check-input" id="check-all"></th>
                            <th>Mã hộ</th>
                            <th>Tên chủ hộ</th>
                            <th>Địa chỉ</th>
                            <th>Loại hộ</th>
                            <th>Kỳ gần nhất</th>
                            <th>Phiếu thu / HĐ gần nhất</th>
                            <th>Ngày thu/xuất gần nhất</th>
                            <th>Trạng thái</th>
                            <th class="w-1 text-end">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="spinner-border text-primary me-2" role="status"></div> Đang tải dữ liệu...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <!-- Pagination Footer -->
            <div class="card-footer d-flex align-items-center border-top py-2">
                <p class="m-0 text-secondary" id="pagination-info">Hiển thị từ 0 đến 0 trong tổng số 0 hộ dân</p>
                <ul class="pagination m-0 ms-auto" id="pagination-list">
                    <!-- Dynamic pagination links -->
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Modal Range Action (Individual & Bulk) -->
<div class="modal modal-blur fade" id="modal-range-payment" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modal-range-title">Xử lý thu phí theo khoảng thời gian</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rangePaymentForm">
                <?= csrf_field() ?>
                <!-- Holds target household IDs -->
                <div id="form-households-container"></div>
                <input type="hidden" name="action" id="form-action" value="pay">

                <div class="modal-body">
                    <div class="alert alert-info py-2" id="range-apply-info">
                        Đang áp dụng cho <strong>1</strong> hộ dân được chọn.
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Chọn năm thu phí</label>
                            <select name="year" id="range_year" class="form-select">
                                <option value="2026" selected>Năm 2026</option>
                                <option value="2027">Năm 2027</option>
                                <option value="2028">Năm 2028</option>
<option value="2029">Năm 2029</option>
                                <option value="2030">Năm 2030</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Từ tháng</label>
                            <select name="from_month" id="range_from" class="form-select">
                                <?php for ($m = 1; $m <= 12; $m++) : ?>
                                    <option value="<?= $m ?>" <?= $m == 1 ? 'selected' : '' ?>>Tháng <?= $m ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label required">Đến tháng</label>
                            <select name="to_month" id="range_to" class="form-select">
                                <?php for ($m = 1; $m <= 12; $m++) : ?>
                                    <option value="<?= $m ?>" <?= $m == date('n') ? 'selected' : '' ?>>Tháng <?= $m ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Live Calculation Panel -->
                    <div class="p-3 bg-light border rounded mb-4 text-center">
                        <div class="row">
                            <div class="col-md-6 border-end">
                                <div class="text-secondary small">Tổng số tháng áp dụng</div>
                                <div class="fs-2 font-weight-bold text-blue mt-1" id="calc-months">0 tháng</div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-secondary small">Tổng tiền thanh toán dự tính (mỗi hộ)</div>
                                <div class="fs-2 font-weight-bold text-success mt-1" id="calc-amount">0 VNĐ</div>
                            </div>
                        </div>
                    </div>

                    <!-- Individual Household history panel (hidden during bulk) -->
                    <div id="individual-history-panel" class="mt-3">
                        <h4 class="mb-2 text-blue"><i class="ti ti-history me-1"></i>Lịch sử thu phí gần đây</h4>
                        <div class="table-responsive" style="max-height: 200px; overflow-y: auto;">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th>Kỳ thu phí</th>
                                        <th>Số tiền</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày nộp</th>
                                        <th>Số phiếu thu</th>
                                        <th>Số HĐ VNPT</th>
                                        <th>Thu ngân</th>
                                        <th class="text-end">Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody id="history-body">
                                    <tr><td colspan="8" class="text-center text-secondary small">Chưa có lịch sử nộp phí.</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Hủy bỏ</button>
                    <?php if (has_permission('payments.create')) : ?>
                    <button type="button" class="btn btn-success" id="btn-submit-pay">
                        <i class="ti ti-cash me-1"></i>Thu tiền mặt
                    </button>
                    <button type="button" class="btn btn-info" id="btn-submit-invoice">
                        <i class="ti ti-file-invoice me-1"></i>Phát hành hóa đơn
                    </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const feeRatesMap = <?= json_encode($feeRates ?? []) ?>;
    let households = [];
    let currentSelectedIds = [];
    let isBulkMode = false;
    let singleHouseholdRatePrice = 30000; // default
    let singleHouseholdVat = 10; // default
    let currentPage = 1;
    let totalPages = 1;

    $(document).ready(function() {
        loadData();

        // Search trigger
        $('#search-input, #route-select, #status-select').on('change keyup', function() {
            currentPage = 1;
            loadData();
        });

        // Select All checkboxes
        $('#check-all').on('change', function() {
            $('.check-item').prop('checked', this.checked);
        });

        $(document).on('change', '.check-item', function() {
            if ($('.check-item:checked').length === $('.check-item').length) {
                $('#check-all').prop('checked', true);
            } else {
                $('#check-all').prop('checked', false);
            }
        });

        // Calculated values triggers
        $('#range_year, #range_from, #range_to').on('change', function() {
            recalculatePreview();
        });

        // Bulk buttons trigger
        $('.btn-bulk-action').on('click', function() {
            const selected = getSelectedIds();
            if (selected.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Cảnh báo', text: 'Vui lòng chọn ít nhất một hộ dân.' });
                return;
            }
            
            isBulkMode = true;
            currentSelectedIds = selected;
            const action = $(this).data('action');

            $('#form-action').val(action);
            $('#modal-range-title').text(action === 'invoice' ? 'Phát hành hóa đơn hàng loạt' : 'Thu tiền mặt hàng loạt');
            $('#range-apply-info').html(`Đang áp dụng cho <strong>${selected.length}</strong> hộ dân được chọn.`);
            $('#individual-history-panel').hide();
            
            // Build hidden inputs
            let inputsHtml = '';
            selected.forEach(id => {
                inputsHtml += `<input type="hidden" name="household_ids[]" value="${id}">`;
            });
            $('#form-households-container').html(inputsHtml);
            
            // Default rate for display during bulk is aggregate/individual in reality
            singleHouseholdRatePrice = 0; // Display note instead of static calculated amount if rates differ
            
            recalculatePreview();
            $('#modal-range-payment').modal('show');
        });

        // Bulk Print trigger
        $('.btn-bulk-print').on('click', function() {
            const selected = getSelectedIds();
            if (selected.length === 0) {
                Swal.fire({ icon: 'warning', title: 'Cảnh báo', text: 'Vui lòng chọn ít nhất một hộ dân để in.' });
                return;
            }

            const printType = $(this).data('type');
            
            // Open a quick modal/alert to ask for print range
            Swal.fire({
                title: 'Chọn kỳ in ấn',
                html: `
                    <div class="row g-2 text-start">
                        <div class="col-12 mb-2">
                            <label class="form-label font-weight-bold">Năm</label>
                            <select id="print_yr" class="form-select">
                                <option value="2026" selected>2026</option>
                                <option value="2027">2027</option>
                                <option value="2025">2025</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label font-weight-bold">Từ tháng</label>
                            <select id="print_fr" class="form-select">
                                ${[...Array(12).keys()].map(i => `<option value="${i+1}">Tháng ${i+1}</option>`).join('')}
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label font-weight-bold">Đến tháng</label>
                            <select id="print_t" class="form-select">
                                ${[...Array(12).keys()].map(i => `<option value="${i+1}" ${i+1 == new Date().getMonth()+1 ? 'selected':''}>Tháng ${i+1}</option>`).join('')}
                            </select>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Xác nhận in',
                cancelButtonText: 'Hủy bỏ',
                preConfirm: () => {
                    const from = parseInt(document.getElementById('print_fr').value);
                    const to = parseInt(document.getElementById('print_t').value);
                    if (to < from) {
                        Swal.showValidationMessage('Tháng kết thúc phải lớn hơn hoặc bằng tháng bắt đầu.');
                    }
                    return {
                        year: document.getElementById('print_yr').value,
                        from: from,
                        to: to
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const data = result.value;
                    
                    // Populate hidden print form
                    $('#print_year').val(data.year);
                    $('#print_from_month').val(data.from);
                    $('#print_to_month').val(data.to);
                    $('#print_type').val(printType);
                    
                    let inputs = '';
                    selected.forEach(id => {
                        inputs += `<input type="hidden" name="household_ids[]" value="${id}">`;
                    });
                    $('#print_households_container').html(inputs);
                    
                    // Submit print form
                    $('#bulkPrintForm').submit();
                }
            });
        });

        // Submit form actions
        $('#btn-submit-pay').on('click', function() {
            $('#form-action').val('pay');
            submitRangePaymentForm();
        });

        $('#btn-submit-invoice').on('click', function() {
            $('#form-action').val('invoice');
            submitRangePaymentForm();
        });
    });

    function getSelectedIds() {
        const ids = [];
        $('.check-item:checked').each(function() {
            ids.push($(this).val());
        });
        return ids;
    }

    function loadData() {
        $('#check-all').prop('checked', false);
        const search = $('#search-input').val();
        const routeId = $('#route-select').val();
        const status = $('#status-select').val();

        $.ajax({
            url: '<?= base_url('payments/list') ?>',
            method: 'GET',
            data: { 
                search: search, 
                route_id: routeId, 
                status: status,
                page: currentPage,
                per_page: 10
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    households = res.data;
                    totalPages = res.total_pages;
                    renderTable(households);
                    renderPagination(res.total, res.page, res.per_page, res.total_pages);
                } else {
                    $('#table-body').html('<tr><td colspan="10" class="text-center text-danger">Lỗi tải danh sách dữ liệu.</td></tr>');
                }
            }
        });
    }

    function renderPagination(total, page, perPage, totalPagesVal) {
        if (total === 0) {
            $('#pagination-info').text('Hiển thị từ 0 đến 0 trong tổng số 0 hộ dân');
            $('#pagination-list').html('');
            return;
        }
        const from = (page - 1) * perPage + 1;
        const to = Math.min(page * perPage, total);
        $('#pagination-info').text(`Hiển thị từ ${from} đến ${to} trong tổng số ${total} hộ dân`);

        let html = '';
        
        // Prev button
        const prevDisabled = page === 1 ? 'disabled' : '';
        html += `<li class="page-item ${prevDisabled}"><a class="page-link" href="#" onclick="goToPage(${page - 1}); return false;"><i class="ti ti-chevron-left me-1"></i>Trước</a></li>`;

        // Numbered buttons
        for (let i = 1; i <= totalPagesVal; i++) {
            const activeClass = i === page ? 'active' : '';
            html += `<li class="page-item ${activeClass}"><a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a></li>`;
        }

        // Next button
        const nextDisabled = page === totalPagesVal ? 'disabled' : '';
        html += `<li class="page-item ${nextDisabled}"><a class="page-link" href="#" onclick="goToPage(${page + 1}); return false;">Sau<i class="ti ti-chevron-right ms-1"></i></a></li>`;

        $('#pagination-list').html(html);
    }

    function goToPage(num) {
        if (num < 1 || num > totalPages) return;
        currentPage = num;
        loadData();
    }

    function formatReceiptCode(code) {
        if (!code) return '';
        const parts = code.split('-');
        if (parts.length === 4) {
            return `${parts[0]}-${parts[1]}-${parts[3]}`;
        }
        return code;
    }

    function renderTable(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="10" class="text-center text-secondary">Không tìm thấy hộ dân nào thỏa mãn bộ lọc.</td></tr>';
        } else {
            data.forEach(item => {
                let statusBadge = '<span class="badge bg-secondary-lt">Chưa thu tiền</span>';
                if (item.latest_status === 'Đã thu tiền') {
                    statusBadge = '<span class="badge bg-warning-lt">Đã thu tiền</span>';
                } else if (item.latest_status === 'Đã xuất hóa đơn') {
                    statusBadge = '<span class="badge bg-success-lt">Đã xuất hóa đơn</span>';
                }

                const latestMonth = item.latest_month ? esc(item.latest_month) : '<span class="text-muted">—</span>';
                
                // Format receipt and show latest VNPT invoice if published
                let receiptContent = '<span class="text-muted">—</span>';
                if (item.latest_receipt) {
                    const formattedCode = formatReceiptCode(item.latest_receipt);
                    const latestVnptBadge = item.latest_vnpt_inv_no
                        ? `<div class="mt-1"><span class="badge bg-teal-lt text-teal" style="font-size: 0.75rem;"><i class="ti ti-file-check me-1"></i>HĐ: ${esc(item.latest_vnpt_inv_no)}</span></div>`
                        : '';
                    receiptContent = `<div><code>${esc(formattedCode)}</code></div>${latestVnptBadge}`;
                }

                const paymentDate = item.latest_date ? format_date(item.latest_date) : '<span class="text-muted">—</span>';

                html += `
                    <tr>
                        <td><input type="checkbox" class="form-check-input check-item" value="${item.id}"></td>
                        <td><span class="text-secondary">${esc(item.household_code)}</span></td>
                        <td><strong>${esc(item.owner_name)}</strong></td>
                        <td>${esc(item.address)}</td>
                        <td><span class="badge bg-blue-lt">${esc(item.household_type)}</span></td>
                        <td><strong>${latestMonth}</strong></td>
                        <td>${receiptContent}</td>
                        <td>${paymentDate}</td>
                        <td>${statusBadge}</td>
                        <td class="text-end">
                            <div class="btn-list justify-content-end flex-nowrap">
                                <button class="btn btn-primary btn-sm" onclick="openIndividualModal(${item.id})">
                                    <i class="ti ti-settings me-1"></i>TÁC VỤ
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        $('#table-body').html(html);
    }

    function openIndividualModal(householdId) {
        const item = households.find(h => h.id == householdId);
        if (!item) return;

        isBulkMode = false;
        currentSelectedIds = [householdId];
        
        // Look up corresponding rate
        const rate = feeRatesMap.find(r => r.household_type === item.household_type);
        singleHouseholdRatePrice = rate ? parseFloat(rate.price) : 30000;
        singleHouseholdVat = rate ? parseFloat(rate.vat) : 10;

        $('#form-households-container').html(`<input type="hidden" name="household_ids[]" value="${householdId}">`);
        $('#modal-range-title').text(`Thu phí & Xuất hóa đơn: ${item.owner_name}`);
        $('#range-apply-info').html(`Hộ dân: <strong>${item.owner_name}</strong> (${item.household_code}) | Loại hộ: <strong>${item.household_type}</strong>`);
        $('#individual-history-panel').show();

        recalculatePreview();
        loadHistory(householdId);
        $('#modal-range-payment').modal('show');
    }

    let currentHistoryHouseholdId = null;

    function loadHistory(householdId) {
        currentHistoryHouseholdId = householdId;
        $('#history-body').html('<tr><td colspan="8" class="text-center small">Đang tải lịch sử...</td></tr>');
        $.ajax({
            url: '<?= base_url('payments/history') ?>/' + householdId,
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status && res.data.length > 0) {
                    let html = '';
                    res.data.forEach(h => {
                        const statusClass = h.payment_status === 'Đã xuất hóa đơn' ? 'bg-success-lt' : 'bg-warning-lt';
                        const vnptBadge = h.vnpt_inv_no
                            ? `<span class="badge bg-teal-lt" title="Số hóa đơn: ${esc(h.vnpt_inv_no)}"><i class="ti ti-file-check me-1"></i>${esc(h.vnpt_inv_no)}</span>`
                            : '<span class="text-muted">Chưa xuất</span>';
                        const canPublish = (h.payment_status === 'Đã thu tiền' || h.payment_status === 'Đã xuất hóa đơn') && !h.vnpt_inv_no;
                        const publishBtn = canPublish
                            ? `<button type="button" class="btn btn-sm btn-outline-teal py-0 px-1 me-1" onclick="publishVnptInvoice(${h.id})" title="Xuất hóa đơn điện tử VNPT"><i class="ti ti-send"></i> HĐĐT</button>`
                            : '';
                        html += `
                            <tr>
                                <td><strong>${esc(h.billing_month)}</strong></td>
                                <td class="text-blue font-weight-medium">${format_money(h.amount)}</td>
                                <td><span class="badge ${statusClass} small">${esc(h.payment_status)}</span></td>
                                <td>${format_date(h.payment_date)}</td>
                                <td><code>${esc(h.receipt_code)}</code></td>
                                <td>${vnptBadge}</td>
                                <td>${esc(h.collector_name || 'Thu ngân')}</td>
                                <td class="text-end">
                                    ${publishBtn}
                                    <button type="button" class="btn btn-sm btn-outline-warning py-0 px-1 me-1" onclick="printHistorical(${h.id}, 'receipt')" title="In phiếu thu">
                                        <i class="ti ti-printer"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="printHistorical(${h.id}, 'invoice')" title="In hóa đơn">
                                        <i class="ti ti-file-invoice"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#history-body').html(html);
                } else {
                    $('#history-body').html('<tr><td colspan="8" class="text-center text-muted small">Chưa có lịch sử nộp phí.</td></tr>');
                }
            }
        });
    }

    function printHistorical(paymentId, type) {
        const url = `<?= base_url('payments/print-record') ?>/${paymentId}?type=${type}`;
        window.open(url, '_blank');
    }

    function recalculatePreview() {
        const from = parseInt($('#range_from').val());
        const to = parseInt($('#range_to').val());
        
        let months = 0;
        if (to >= from) {
            months = (to - from) + 1;
        }

        $('#calc-months').text(`${months} tháng`);
        
        if (isBulkMode) {
            $('#calc-amount').html('<span class="text-muted small">(Tùy theo loại từng hộ)</span>');
        } else {
            const subtotal = months * singleHouseholdRatePrice;
            const tax = subtotal * (singleHouseholdVat / 100);
            const total = subtotal + tax;
            $('#calc-amount').html(`${format_money(total)} <br><span class="text-muted small" style="font-size: 11px;">(Đơn giá: ${format_money(singleHouseholdRatePrice)} + VAT: ${singleHouseholdVat}%)</span>`);
        }
    }

    function submitRangePaymentForm() {
        const from = parseInt($('#range_from').val());
        const to = parseInt($('#range_to').val());
        if (to < from) {
            Swal.fire({ icon: 'warning', title: 'Cảnh báo', text: 'Tháng kết thúc phải lớn hơn hoặc bằng tháng bắt đầu.' });
            return;
        }

        const submitBtn = $('#btn-submit-pay, #btn-submit-invoice');
        submitBtn.prop('disabled', true);

        $.ajax({
            url: '<?= base_url('payments/process') ?>',
            method: 'POST',
            data: $('#rangePaymentForm').serialize(),
            dataType: 'json',
            success: function(res) {
                submitBtn.prop('disabled', false);
                if (res.status) {
                    $('#modal-range-payment').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'Thành công',
                        text: res.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => { loadData(); });
                } else {
                    Swal.fire({ icon: 'error', title: 'Lỗi', text: res.message });
                }
            },
            error: function() {
                submitBtn.prop('disabled', false);
                Swal.fire({ icon: 'error', title: 'Lỗi', text: 'Lỗi kết nối máy chủ.' });
            }
        });
    }

    function printSingle(householdId, printType) {
        // Retrieve print range via quick prompt
        Swal.fire({
            title: 'Chọn kỳ in ấn',
            html: `
                <div class="row g-2 text-start">
                    <div class="col-12 mb-2">
                        <label class="form-label font-weight-bold">Năm</label>
                        <select id="pr_yr" class="form-select">
                            <option value="2026" selected>2026</option>
                            <option value="2027">2027</option>
                            <option value="2025">2025</option>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label font-weight-bold">Từ tháng</label>
                        <select id="pr_fr" class="form-select">
                            ${[...Array(12).keys()].map(i => `<option value="${i+1}">Tháng ${i+1}</option>`).join('')}
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="form-label font-weight-bold">Đến tháng</label>
                        <select id="pr_t" class="form-select">
                            ${[...Array(12).keys()].map(i => `<option value="${i+1}" ${i+1 == new Date().getMonth()+1 ? 'selected':''}>Tháng ${i+1}</option>`).join('')}
                        </select>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Xem in',
            cancelButtonText: 'Hủy bỏ',
            preConfirm: () => {
                const from = parseInt(document.getElementById('pr_fr').value);
                const to = parseInt(document.getElementById('pr_t').value);
                if (to < from) {
                    Swal.showValidationMessage('Tháng kết thúc phải lớn hơn hoặc bằng tháng bắt đầu.');
                }
                return {
                    year: document.getElementById('pr_yr').value,
                    from: from,
                    to: to
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const data = result.value;
                const url = `<?= base_url('payments/receipt') ?>/${householdId}?year=${data.year}&from=${data.from}&to=${data.to}`;
                window.open(url, '_blank');
            }
        });
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

    /**
     * Publish a payment record as a VNPT electronic invoice (HĐĐT).
     * @param {number} paymentId - ID of the payments record
     */
    function publishVnptInvoice(paymentId) {
        Swal.fire({
            icon: 'question',
            title: 'Xác nhận xuất hóa đơn điện tử',
            html: 'Bạn có chắc chắn muốn xuất <strong>Hóa đơn Điện tử VNPT</strong> cho phiếu thu này?<br><small class="text-muted">Hành động này không thể hoàn tác sau khi hóa đơn đã được gửi lên VNPT.</small>',
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-send me-1"></i>Xuất HĐĐT',
            cancelButtonText: 'Hủy',
            confirmButtonColor: '#0ca678',
        }).then((result) => {
            if (!result.isConfirmed) return;

            Swal.fire({
                title: 'Đang xuất hóa đơn...',
                html: 'Đang kết nối với hệ thống VNPT, vui lòng đợi...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: '<?= base_url('payments/publish-invoice') ?>/' + paymentId,
                method: 'POST',
                data: { '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Xuất HĐĐT thành công!',
                            html: res.message,
                            confirmButtonText: 'OK'
                        }).then(() => {
                            // Reload history to reflect VNPT invoice number
                            if (typeof currentHistoryHouseholdId !== 'undefined' && currentHistoryHouseholdId) {
                                loadHistory(currentHistoryHouseholdId);
                            }
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Xuất thất bại', html: res.message });
                    }
                },
                error: function(xhr) {
                    Swal.fire({ icon: 'error', title: 'Lỗi kết nối', text: 'Không thể kết nối máy chủ. Vui lòng kiểm tra lại.' });
                }
            });
        });
    }
</script>
<?= $this->endSection() ?>
