<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Quản lý hộ dân<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<ol class="breadcrumb breadcrumb-arrows" aria-label="breadcrumbs">
    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Trang chủ</a></li>
    <li class="breadcrumb-item active" aria-current="page"><a href="#">Hộ dân</a></li>
</ol>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Danh sách hộ dân thu gom rác</h3>
                <div class="card-options">
                    <?php if (has_permission('households.create')) : ?>
                    <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modal-import-household">
                        <i class="ti ti-table-import me-2"></i>Import Excel
                    </button>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add">
                        <i class="ti ti-plus me-2"></i>Thêm hộ dân
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Search & Filter Panel -->
            <div class="card-body border-bottom py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-5">
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
                    <div class="col-md-3">
                        <label class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" id="trash-switch" value="1">
                            <span class="form-check-label font-weight-medium text-warning">Xem thùng rác (đã xóa)</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="table-responsive p-3">
                <table id="householdsTable" class="table table-striped table-hover card-table table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th>Mã hộ</th>
                            <th>Chủ hộ</th>
                            <th>CCCD</th>
                            <th>Số điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Loại hộ</th>
                            <th>Tuyến thu gom</th>
                            <th>Trạng thái</th>
                            <th class="w-1">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <!-- Filled by AJAX -->
                        <tr>
                            <td colspan="9" class="text-center py-4">
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

<!-- Modal Add -->
<div class="modal modal-blur fade" id="modal-add" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm mới hộ dân</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Tên chủ hộ</label>
                            <input type="text" name="owner_name" class="form-control" placeholder="Họ và tên chủ hộ">
                            <div class="invalid-feedback err_owner_name"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CCCD/Mã số định danh</label>
                            <input type="text" name="id_card" class="form-control" placeholder="CCCD gồm 12 số">
                            <div class="invalid-feedback err_id_card"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" class="form-control" placeholder="Số điện thoại liên hệ">
                            <div class="invalid-feedback err_phone"></div>
                        </div>
                        <input type="hidden" name="members_count" value="1">
                        <input type="hidden" name="ward_group" value="">
                        <input type="hidden" name="ward" value="">
                        <div class="col-md-12 mb-3">
                            <label class="form-label required">Địa chỉ cụ thể</label>
                            <input type="text" name="address" class="form-control" placeholder="Ví dụ: Số 12A, ngõ 5 Nguyễn Du">
                            <div class="invalid-feedback err_address"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Loại hộ dân (Áp dụng mức phí)</label>
                            <select name="household_type" class="form-select">
                                <option value="">--- Chọn loại hộ ---</option>
                                <?php foreach($feeRates as $rate): ?>
                                    <option value="<?= esc($rate['household_type']) ?>"><?= esc($rate['household_type']) ?> (<?= format_money($rate['price']) ?>/tháng)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback err_household_type"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tuyến thu gom phân công</label>
                            <select name="route_id" class="form-select">
                                <option value="">Chưa phân tuyến</option>
                                <?php foreach($routes as $route): ?>
                                    <option value="<?= $route['id'] ?>"><?= esc($route['route_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback err_route_id"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Trạng thái</label>
                            <select name="status" class="form-select">
                                <option value="Đang hoạt động">Đang hoạt động</option>
                                <option value="Tạm ngưng">Tạm ngưng</option>
                            </select>
                            <div class="invalid-feedback err_status"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tọa độ GPS (Vĩ độ, Kinh độ)</label>
                            <input type="text" name="gps" class="form-control" placeholder="Ví dụ: 21.0228, 105.8519">
                            <div class="invalid-feedback err_gps"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary">Lưu lại</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal modal-blur fade" id="modal-edit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cập nhật thông tin hộ dân</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Tên chủ hộ</label>
                            <input type="text" name="owner_name" id="edit_owner_name" class="form-control" placeholder="Họ và tên chủ hộ">
                            <div class="invalid-feedback err_owner_name"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">CCCD/Mã số định danh</label>
                            <input type="text" name="id_card" id="edit_id_card" class="form-control" placeholder="CCCD gồm 12 số">
                            <div class="invalid-feedback err_id_card"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Số điện thoại</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control" placeholder="Số điện thoại liên hệ">
                            <div class="invalid-feedback err_phone"></div>
                        </div>
                        <input type="hidden" name="members_count" id="edit_members_count" value="1">
                        <input type="hidden" name="ward_group" id="edit_ward_group" value="">
                        <input type="hidden" name="ward" id="edit_ward" value="">
                        <div class="col-md-12 mb-3">
                            <label class="form-label required">Địa chỉ cụ thể</label>
                            <input type="text" name="address" id="edit_address" class="form-control" placeholder="Ví dụ: Số 12A, ngõ 5 Nguyễn Du">
                            <div class="invalid-feedback err_address"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Loại hộ dân (Áp dụng mức phí)</label>
                            <select name="household_type" id="edit_household_type" class="form-select">
                                <option value="">--- Chọn loại hộ ---</option>
                                <?php foreach($feeRates as $rate): ?>
                                    <option value="<?= esc($rate['household_type']) ?>"><?= esc($rate['household_type']) ?> (<?= format_money($rate['price']) ?>/tháng)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback err_household_type"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tuyến thu gom phân công</label>
                            <select name="route_id" id="edit_route_id" class="form-select">
                                <option value="">Chưa phân tuyến</option>
                                <?php foreach($routes as $route): ?>
                                    <option value="<?= $route['id'] ?>"><?= esc($route['route_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback err_route_id"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Trạng thái</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Đang hoạt động">Đang hoạt động</option>
                                <option value="Tạm ngưng">Tạm ngưng</option>
                            </select>
                            <div class="invalid-feedback err_status"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tọa độ GPS (Vĩ độ, Kinh độ)</label>
                            <input type="text" name="gps" id="edit_gps" class="form-control" placeholder="Ví dụ: 21.0228, 105.8519">
                            <div class="invalid-feedback err_gps"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    let households = [];
    let currentPage = 1;
    let totalPages = 1;
    let showDeleted = 0;

    $(document).ready(function() {
        loadHouseholds();

        // Add form submission
        $('#addForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors('#addForm');
            
            $.ajax({
                url: '<?= base_url('households/create') ?>',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        $('#modal-add').modal('hide');
                        $('#addForm')[0].reset();
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            currentPage = 1;
                            loadHouseholds();
                        });
                    } else {
                        if (res.errors) {
                            displayErrors('#addForm', res.errors);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Thất bại',
                                text: res.message
                            });
                        }
                    }
                }
            });
        });

        // Edit form submission
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors('#editForm');
            const id = $('#edit_id').val();
            
            $.ajax({
                url: '<?= base_url('households/update') ?>/' + id,
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    if (res.status) {
                        $('#modal-edit').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            loadHouseholds();
                        });
                    } else {
                        if (res.errors) {
                            displayErrors('#editForm', res.errors);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Thất bại',
                                text: res.message
                            });
                        }
                    }
                }
            });
        });

        // Live filters
        $('#search-input, #route-select').on('change keyup', function() {
            currentPage = 1;
            loadHouseholds();
        });

        // Trash switch
        $('#trash-switch').on('change', function() {
            showDeleted = this.checked ? 1 : 0;
            currentPage = 1;
            loadHouseholds();
        });
    });

    // Load households function
    function loadHouseholds() {
        const search = $('#search-input').val();
        const routeId = $('#route-select').val();

        $.ajax({
            url: '<?= base_url('households/list') ?>',
            method: 'GET',
            data: { 
                search: search, 
                route_id: routeId,
                page: currentPage,
                per_page: 10,
                show_deleted: showDeleted
            },
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    households = res.data;
                    totalPages = res.total_pages;
                    renderTable(households);
                    renderPagination(res.total, res.page, res.per_page, res.total_pages);
                } else {
                    $('#table-body').html('<tr><td colspan="9" class="text-center text-danger">Không thể tải dữ liệu: ' + res.message + '</td></tr>');
                }
            },
            error: function() {
                $('#table-body').html('<tr><td colspan="9" class="text-center text-danger">Không thể kết nối đến máy chủ.</td></tr>');
            }
        });
    }

    // Render table rows
    function renderTable(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="9" class="text-center text-secondary py-4">Không tìm thấy hộ dân nào.</td></tr>';
        } else {
            data.forEach(item => {
                const statusBadge = item.status === 'Đang hoạt động' 
                    ? '<span class="badge bg-success-lt">Đang hoạt động</span>' 
                    : '<span class="badge bg-warning-lt">Tạm ngưng</span>';
                
                const routeName = item.route_name ? esc(item.route_name) : '<span class="text-muted">Chưa phân tuyến</span>';
                const idCard = item.id_card ? esc(item.id_card) : '-';
                const phone = item.phone ? esc(item.phone) : '-';
                
                let actionBtn = '';
                if (showDeleted === 1) {
                    actionBtn = `
                        <button class="btn btn-success btn-sm" onclick="confirmAction('<?= base_url('households/restore') ?>/${item.id}', 'Khôi phục hộ dân?', 'Hộ dân này sẽ được khôi phục trở lại trạng thái hoạt động bình thường.')">
                            <i class="ti ti-refresh me-1"></i>Khôi phục
                        </button>
                    `;
                } else {
                    actionBtn = `
                        <?php if (has_permission('households.edit')) : ?>
                        <button class="btn btn-secondary btn-sm" onclick="openEditModal(${item.id})">
                            <i class="ti ti-edit me-1"></i>Sửa
                        </button>
                        <?php endif; ?>
                        <?php if (has_permission('households.delete')) : ?>
                        <button class="btn btn-danger btn-sm" onclick="confirmAction('<?= base_url('households/delete') ?>/${item.id}', 'Xóa hộ dân?', 'Tài khoản và thông tin hộ dân này sẽ bị xóa mềm.')">
                            <i class="ti ti-trash me-1"></i>Xóa
                        </button>
                        <?php endif; ?>
                    `;
                }

                html += `
                    <tr>
                        <td><span class="text-secondary font-weight-medium">${esc(item.household_code)}</span></td>
                        <td><strong>${esc(item.owner_name)}</strong></td>
                        <td>${idCard}</td>
                        <td>${phone}</td>
                        <td>${esc(item.address)}</td>
                        <td><span class="badge bg-blue-lt">${esc(item.household_type)}</span></td>
                        <td>${routeName}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                ${actionBtn}
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        $('#table-body').html(html);
    }

    // Render pagination controls
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
        loadHouseholds();
    }

    // Open edit modal and populate values
    function openEditModal(id) {
        const item = households.find(h => h.id == id);
        if (!item) return;

        // Clear existing errors
        clearErrors('#editForm');

        $('#edit_id').val(item.id);
        $('#edit_owner_name').val(item.owner_name);
        $('#edit_id_card').val(item.id_card);
        $('#edit_phone').val(item.phone);
        $('#edit_members_count').val(item.members_count);
        $('#edit_address').val(item.address);
        $('#edit_ward_group').val(item.ward_group);
        $('#edit_ward').val(item.ward);
        $('#edit_household_type').val(item.household_type);
        $('#edit_route_id').val(item.route_id || '');
        $('#edit_status').val(item.status);
        $('#edit_gps').val(item.gps || '');

        $('#modal-edit').modal('show');
    }

    // Helper functions for showing / clearing validation errors
    function displayErrors(formSelector, errors) {
        Object.keys(errors).forEach(field => {
            const input = $(formSelector + ' [name="' + field + '"]');
            input.addClass('is-invalid');
            $(formSelector + ' .err_' + field).text(errors[field]);
        });
    }

    function clearErrors(formSelector) {
        $(formSelector + ' .form-control, ' + formSelector + ' .form-select').removeClass('is-invalid');
        $(formSelector + ' .invalid-feedback').text('');
    }

    // Escape HTML strings helper
    function esc(string) {
        if (!string) return '';
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return string.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
</script>

<!-- Modal Import Households -->
<div class="modal modal-blur fade" id="modal-import-household" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-table-import me-2 text-success"></i>Import Hộ dân từ Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="importHouseholdForm" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chọn file Excel / CSV</label>
                        <input type="file" name="import_file" id="importHouseholdFile" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <div class="form-hint mt-1">
                            Định dạng hỗ trợ: <strong>.xlsx, .xls, .csv</strong> &mdash;
                            <a href="<?= base_url('households/template') ?>" target="_blank" class="text-success">
                                <i class="ti ti-download me-1"></i>Tải file mẫu
                            </a>
                        </div>
                    </div>
                    <div class="alert alert-info p-2 mb-0" style="font-size:0.85rem;">
                        <strong>Các cột yêu cầu:</strong><br>
                        <code>Tên chủ hộ</code> | <code>CCCD</code> | <code>Số điện thoại</code> | <code>Địa chỉ</code> | <code>Loại hộ dân</code> | <code>Mã tuyến thu gom</code> | <code>Trạng thái</code> | <code>GPS</code> (tùy chọn)
                    </div>
                    <div id="importHouseholdErrors" class="mt-3" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-success" id="btnImportHousehold">
                        <i class="ti ti-upload me-2"></i>Bắt đầu Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('#importHouseholdForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btnImportHousehold').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...');
        const fd = new FormData(this);
        $.ajax({
            url: '<?= base_url('households/import') ?>',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(res) {
                btn.prop('disabled', false).html('<i class="ti ti-upload me-2"></i>Bắt đầu Import');
                if (res.status) {
                    let warningHtml = '';
                    if (res.warnings && res.warnings.length > 0) {
                        warningHtml = '<div class="alert alert-warning mt-2"><strong>Cảnh báo (' + res.warnings.length + ' dòng bỏ qua):</strong><ul class="mb-0 mt-1">' +
                            res.warnings.map(w => '<li>' + w + '</li>').join('') + '</ul></div>';
                    }
                    $('#modal-import-household').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Import thành công', html: res.message + warningHtml, confirmButtonText: 'OK' })
                        .then(() => { $('#importHouseholdForm')[0].reset(); loadHouseholds(); });
                } else {
                    const errHtml = '<div class="alert alert-danger"><strong>Lỗi:</strong><ul class="mb-0 mt-1">' +
                        (res.errors || [res.message]).map(e => '<li>' + e + '</li>').join('') + '</ul></div>';
                    $('#importHouseholdErrors').html(errHtml).show();
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="ti ti-upload me-2"></i>Bắt đầu Import');
                Swal.fire({ icon: 'error', title: 'Lỗi kết nối', text: 'Không thể kết nối đến máy chủ.' });
            }
        });
    });
    $('#modal-import-household').on('hidden.bs.modal', function() {
        $('#importHouseholdErrors').hide().html('');
        $('#importHouseholdForm')[0].reset();
    });
</script>
<?= $this->endSection() ?>
