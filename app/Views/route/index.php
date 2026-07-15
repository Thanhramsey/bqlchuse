<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Quản lý tuyến thu gom<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<ol class="breadcrumb breadcrumb-arrows" aria-label="breadcrumbs">
    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Trang chủ</a></li>
    <li class="breadcrumb-item active" aria-current="page"><a href="#">Tuyến thu gom</a></li>
</ol>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Danh sách phân cấp tuyến thu gom rác thải</h3>
                <div class="card-options">
                    <?php if (has_permission('routes.create')) : ?>
                    <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#modal-import-route">
                        <i class="ti ti-table-import me-2"></i>Import Excel
                    </button>
                    <button class="btn btn-primary" onclick="openAddModal()">
                        <i class="ti ti-plus me-2"></i>Thêm tuyến mới
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="table-responsive p-3">
                <table id="routesTable" class="table table-striped table-hover card-table table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th>Mã tuyến</th>
                            <th>Tên tuyến thu gom</th>
                            <th>Cấp độ</th>
                            <th>Tuyến cha trực thuộc</th>
                            <th>Nhân viên phụ trách</th>
                            <th>Trạng thái</th>
                            <th class="w-1">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="spinner-border text-primary me-2" role="status"></div> Đang tải dữ liệu...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Add -->
<div class="modal modal-blur fade" id="modal-add" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm tuyến thu gom mới</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Mã tuyến</label>
                            <input type="text" name="route_code" class="form-control" placeholder="Ví dụ: T01.01">
                            <div class="invalid-feedback err_route_code"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Tên tuyến thu gom</label>
                            <input type="text" name="route_name" class="form-control" placeholder="Ví dụ: Tổ 1 - Phố Nguyễn Du">
                            <div class="invalid-feedback err_route_name"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Thuộc tuyến cha (Không chọn nếu là tuyến lớn)</label>
                            <select name="parent_id" id="add_parent_id" class="form-select">
                                <option value="">-- Là tuyến cha (Cao nhất) --</option>
                                <?php foreach ($parents as $p) : ?>
                                    <option value="<?= $p['id'] ?>"><?= esc($p['route_code']) ?> - <?= esc($p['route_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback err_parent_id"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Trạng thái hoạt động</label>
                            <select name="status" class="form-select">
                                <option value="Hoạt động">Hoạt động</option>
                                <option value="Tạm ngưng">Tạm ngưng</option>
                            </select>
                            <div class="invalid-feedback err_status"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label mb-2">Phân tuyến nhân viên phụ trách thu phí</label>
                        <div class="bg-light p-3 border rounded" style="max-height: 180px; overflow-y: auto;">
                            <div class="row">
                                <?php if (empty($staff)) : ?>
                                    <div class="col-12 text-muted small">Không tìm thấy nhân viên thu ngân nào đang hoạt động.</div>
                                <?php else : ?>
                                    <?php foreach ($staff as $s) : ?>
                                        <div class="col-md-6 mb-2">
                                            <label class="form-check">
                                                <input type="checkbox" name="assigned_staff_ids[]" value="<?= $s['id'] ?>" class="form-check-input">
                                                <span class="form-check-label"><?= esc($s['fullname']) ?> <span class="badge bg-secondary-lt ms-1"><?= esc($s['role']) ?></span></span>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
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
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sửa thông tin tuyến thu gom</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Mã tuyến</label>
                            <input type="text" name="route_code" id="edit_route_code" class="form-control">
                            <div class="invalid-feedback err_route_code"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Tên tuyến thu gom</label>
                            <input type="text" name="route_name" id="edit_route_name" class="form-control">
                            <div class="invalid-feedback err_route_name"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Thuộc tuyến cha</label>
                            <select name="parent_id" id="edit_parent_id" class="form-select">
                                <option value="">-- Là tuyến cha (Cao nhất) --</option>
                                <!-- Populated dynamically dynamically excluding self -->
                            </select>
                            <div class="invalid-feedback err_parent_id"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Trạng thái hoạt động</label>
                            <select name="status" id="edit_status" class="form-select">
                                <option value="Hoạt động">Hoạt động</option>
                                <option value="Tạm ngưng">Tạm ngưng</option>
                            </select>
                            <div class="invalid-feedback err_status"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label mb-2">Phân tuyến nhân viên phụ trách thu phí</label>
                        <div class="bg-light p-3 border rounded" style="max-height: 180px; overflow-y: auto;">
                            <div class="row" id="edit_staff_container">
                                <?php foreach ($staff as $s) : ?>
                                    <div class="col-md-6 mb-2">
                                        <label class="form-check">
                                            <input type="checkbox" name="assigned_staff_ids[]" value="<?= $s['id'] ?>" class="form-check-input edit-staff-checkbox" id="edit_staff_<?= $s['id'] ?>">
                                            <span class="form-check-label"><?= esc($s['fullname']) ?> <span class="badge bg-secondary-lt ms-1"><?= esc($s['role']) ?></span></span>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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
    let routes = [];
    let parentOptions = [];

    $(document).ready(function() {
        loadRoutes();

        // Submit Add
        $('#addForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors('#addForm');
            $.ajax({
                url: '<?= base_url('routes/create') ?>',
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
                        }).then(() => { loadRoutes(); });
                    } else {
                        if (res.errors) displayErrors('#addForm', res.errors);
                        else Swal.fire({ icon: 'error', title: 'Lỗi', text: res.message });
                    }
                }
            });
        });

        // Submit Edit
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors('#editForm');
            const id = $('#edit_id').val();
            $.ajax({
                url: '<?= base_url('routes/update') ?>/' + id,
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
                        }).then(() => { loadRoutes(); });
                    } else {
                        if (res.errors) displayErrors('#editForm', res.errors);
                        else Swal.fire({ icon: 'error', title: 'Lỗi', text: res.message });
                    }
                }
            });
        });
    });

    function loadRoutes() {
        $.ajax({
            url: '<?= base_url('routes/list') ?>',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    routes = res.data;
                    parentOptions = res.parents;
                    renderTable(routes);
                    updateAddParentSelect(parentOptions);
                } else {
                    $('#table-body').html('<tr><td colspan="7" class="text-center text-danger">Lỗi: ' + res.message + '</td></tr>');
                }
            }
        });
    }

    function renderTable(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="7" class="text-center text-secondary">Chưa có tuyến thu gom nào.</td></tr>';
        } else {
            data.forEach(item => {
                const statusBadge = item.status === 'Hoạt động' 
                    ? '<span class="badge bg-success-lt">Hoạt động</span>' 
                    : '<span class="badge bg-warning-lt">Tạm ngưng</span>';
                
                const typeBadge = item.parent_id === null
                    ? '<span class="badge bg-purple-lt">Tuyến cha</span>'
                    : '<span class="badge bg-indigo-lt">Tuyến con</span>';

                const parentName = item.parent_name 
                    ? `<code>${esc(item.parent_name)}</code>` 
                    : '<span class="text-muted">—</span>';

                // Display list of staff
                let staffHtml = '';
                if (item.assigned_staff.length === 0) {
                    staffHtml = '<span class="text-muted small">Chưa phân công</span>';
                } else {
                    const names = item.assigned_staff.map(s => `${esc(s.fullname)} (${esc(s.role)})`);
                    staffHtml = `<span class="badge bg-info-lt" data-bs-toggle="tooltip" title="${names.join(', ')}">${item.assigned_staff.length} nhân sự</span>`;
                }

                // Indentation prefix for child routes to reflect tree hierarchy visual
                const prefix = item.parent_id !== null ? '— ' : '';

                html += `
                    <tr>
                        <td><code>${esc(item.route_code)}</code></td>
                        <td><strong>${prefix}${esc(item.route_name)}</strong></td>
                        <td>${typeBadge}</td>
                        <td>${parentName}</td>
                        <td>${staffHtml}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <?php if (has_permission('routes.edit')) : ?>
                                <button class="btn btn-secondary btn-sm" onclick="openEditModal(${item.id})">
                                    <i class="ti ti-edit me-1"></i>Sửa
                                </button>
                                <?php endif; ?>
                                <?php if (has_permission('routes.delete')) : ?>
                                <button class="btn btn-danger btn-sm" onclick="confirmAction('<?= base_url('routes/delete') ?>/${item.id}', 'Xóa tuyến thu gom?', 'Tuyến này và phân công nhân sự tương ứng sẽ bị xóa khỏi hệ thống.')">
                                    <i class="ti ti-trash me-1"></i>Xóa
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                `;
            });
        }
        $('#table-body').html(html);
        $('[data-bs-toggle="tooltip"]').tooltip();
    }

    function updateAddParentSelect(parents) {
        let options = '<option value="">-- Là tuyến cha (Cao nhất) --</option>';
        parents.forEach(p => {
            options += `<option value="${p.id}">${esc(p.route_code)} - ${esc(p.route_name)}</option>`;
        });
        $('#add_parent_id').html(options);
    }

    function openAddModal() {
        clearErrors('#addForm');
        $('#addForm')[0].reset();
        $('#modal-add').modal('show');
    }

    function openEditModal(id) {
        const item = routes.find(r => r.id == id);
        if (!item) return;
        clearErrors('#editForm');
        $('#editForm')[0].reset();

        $('#edit_id').val(item.id);
        $('#edit_route_code').val(item.route_code);
        $('#edit_route_name').val(item.route_name);
        $('#edit_status').val(item.status);

        // Dynamically build parents list excluding current route itself
        let options = '<option value="">-- Là tuyến cha (Cao nhất) --</option>';
        parentOptions.forEach(p => {
            if (p.id != item.id) {
                const selected = p.id == item.parent_id ? 'selected' : '';
                options += `<option value="${p.id}" ${selected}>${esc(p.route_code)} - ${esc(p.route_name)}</option>`;
            }
        });
        $('#edit_parent_id').html(options);

        // Tick checkboxes for assigned staff
        $('.edit-staff-checkbox').prop('checked', false);
        if (item.assigned_staff_ids) {
            item.assigned_staff_ids.forEach(uid => {
                $('#edit_staff_' + uid).prop('checked', true);
            });
        }

        $('#modal-edit').modal('show');
    }

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

    function esc(string) {
        if (!string) return '';
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return string.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
</script>

<!-- Modal Import Routes -->
<div class="modal modal-blur fade" id="modal-import-route" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="ti ti-table-import me-2 text-success"></i>Import Tuyến thu gom từ Excel</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="importRouteForm" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Chọn file Excel / CSV</label>
                        <input type="file" name="import_file" id="importRouteFile" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <div class="form-hint mt-1">
                            Định dạng hỗ trợ: <strong>.xlsx, .xls, .csv</strong> &mdash;
                            <a href="<?= base_url('routes/template') ?>" target="_blank" class="text-success">
                                <i class="ti ti-download me-1"></i>Tải file mẫu
                            </a>
                        </div>
                    </div>
                    <div class="alert alert-info p-2 mb-0" style="font-size:0.85rem;">
                        <strong>Các cột yêu cầu:</strong><br>
                        <code>Mã tuyến</code> | <code>Tên tuyến</code> | <code>Mã tuyến cha</code> (tùy chọn) | <code>Trạng thái</code>
                    </div>
                    <div id="importRouteErrors" class="mt-3" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-link link-secondary me-auto" data-bs-dismiss="modal">Hủy bỏ</button>
                    <button type="submit" class="btn btn-success" id="btnImportRoute">
                        <i class="ti ti-upload me-2"></i>Bắt đầu Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $('#importRouteForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $('#btnImportRoute').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Đang xử lý...');
        const fd = new FormData(this);
        $.ajax({
            url: '<?= base_url('routes/import') ?>',
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
                    $('#modal-import-route').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Import thành công', html: res.message + warningHtml, confirmButtonText: 'OK' })
                        .then(() => { $('#importRouteForm')[0].reset(); loadRoutes(); });
                } else {
                    const errHtml = '<div class="alert alert-danger"><strong>Lỗi:</strong><ul class="mb-0 mt-1">' +
                        (res.errors || [res.message]).map(e => '<li>' + e + '</li>').join('') + '</ul></div>';
                    $('#importRouteErrors').html(errHtml).show();
                }
            },
            error: function() {
                btn.prop('disabled', false).html('<i class="ti ti-upload me-2"></i>Bắt đầu Import');
                Swal.fire({ icon: 'error', title: 'Lỗi kết nối', text: 'Không thể kết nối đến máy chủ.' });
            }
        });
    });
    $('#modal-import-route').on('hidden.bs.modal', function() {
        $('#importRouteErrors').hide().html('');
        $('#importRouteForm')[0].reset();
    });
</script>
<?= $this->endSection() ?>
