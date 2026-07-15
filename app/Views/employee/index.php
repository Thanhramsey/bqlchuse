<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Quản lý nhân viên<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<ol class="breadcrumb breadcrumb-arrows" aria-label="breadcrumbs">
    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Trang chủ</a></li>
    <li class="breadcrumb-item active" aria-current="page"><a href="#">Nhân viên</a></li>
</ol>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Danh sách tài khoản nhân viên phân quyền</h3>
                <div class="card-options">
                    <?php if (has_permission('employees.create')) : ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add">
                        <i class="ti ti-plus me-2"></i>Thêm nhân viên
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="table-responsive p-3">
                <table id="employeesTable" class="table table-striped table-hover card-table table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th>Họ và tên</th>
                            <th>Tài khoản</th>
                            <th>Vai trò / Chức vụ</th>
                            <th>Ca làm việc</th>
                            <th>Trạng thái</th>
                            <th>Đăng nhập cuối</th>
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
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm mới nhân viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Tên tài khoản</label>
                        <input type="text" name="username" class="form-control" placeholder="Tài khoản đăng nhập">
                        <div class="invalid-feedback err_username"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Mật khẩu</label>
                        <input type="password" name="password" class="form-control" placeholder="Mật khẩu tối thiểu 6 ký tự">
                        <div class="invalid-feedback err_password"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Họ và tên</label>
                        <input type="text" name="fullname" class="form-control" placeholder="Họ và tên nhân viên">
                        <div class="invalid-feedback err_fullname"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Vai trò phân quyền</label>
                        <select name="role" class="form-select">
                            <option value="Admin">Quản trị viên (Admin)</option>
                            <option value="Nhân viên">Nhân viên thu gom (Tài xế/Lao công)</option>
                            <option value="Thu ngân">Thu ngân (Thu phí viên)</option>
                            <option value="Kế toán">Kế toán</option>
                            <option value="Lãnh đạo">Lãnh đạo phường/xã</option>
                        </select>
                        <div class="invalid-feedback err_role"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ca làm việc (Chỉ dành cho NV thu gom)</label>
                        <select name="shift" class="form-select">
                            <option value="">Chưa phân ca</option>
                            <option value="Ca sáng">Ca sáng (07:30 - 11:30)</option>
                            <option value="Ca chiều">Ca chiều (13:30 - 17:30)</option>
                            <option value="Ca tối">Ca tối (18:00 - 22:00)</option>
                        </select>
                        <div class="invalid-feedback err_shift"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Trạng thái tài khoản</label>
                        <select name="status" class="form-select">
                            <option value="Hoạt động">Hoạt động</option>
                            <option value="Tạm khóa">Tạm khóa</option>
                        </select>
                        <div class="invalid-feedback err_status"></div>
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
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sửa thông tin nhân viên</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Tên tài khoản</label>
                        <input type="text" name="username" id="edit_username" class="form-control">
                        <div class="invalid-feedback err_username"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mật khẩu mới (Bỏ trống nếu không đổi)</label>
                        <input type="password" name="password" id="edit_password" class="form-control" placeholder="Mật khẩu tối thiểu 6 ký tự">
                        <div class="invalid-feedback err_password"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Họ và tên</label>
                        <input type="text" name="fullname" id="edit_fullname" class="form-control">
                        <div class="invalid-feedback err_fullname"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Vai trò phân quyền</label>
                        <select name="role" id="edit_role" class="form-select">
                            <option value="Admin">Quản trị viên (Admin)</option>
                            <option value="Nhân viên">Nhân viên thu gom (Tài xế/Lao công)</option>
                            <option value="Thu ngân">Thu ngân (Thu phí viên)</option>
                            <option value="Kế toán">Kế toán</option>
                            <option value="Lãnh đạo">Lãnh đạo phường/xã</option>
                        </select>
                        <div class="invalid-feedback err_role"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ca làm việc (Chỉ dành cho NV thu gom)</label>
                        <select name="shift" id="edit_shift" class="form-select">
                            <option value="">Chưa phân ca</option>
                            <option value="Ca sáng">Ca sáng (07:30 - 11:30)</option>
                            <option value="Ca chiều">Ca chiều (13:30 - 17:30)</option>
                            <option value="Ca tối">Ca tối (18:00 - 22:00)</option>
                        </select>
                        <div class="invalid-feedback err_shift"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Trạng thái tài khoản</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="Hoạt động">Hoạt động</option>
                            <option value="Tạm khóa">Tạm khóa</option>
                        </select>
                        <div class="invalid-feedback err_status"></div>
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
    let employees = [];

    $(document).ready(function() {
        loadEmployees();

        // Add
        $('#addForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors('#addForm');
            $.ajax({
                url: '<?= base_url('employees/create') ?>',
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
                        }).then(() => { loadEmployees(); });
                    } else {
                        if (res.errors) displayErrors('#addForm', res.errors);
                        else Swal.fire({ icon: 'error', title: 'Lỗi', text: res.message });
                    }
                }
            });
        });

        // Edit
        $('#editForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors('#editForm');
            const id = $('#edit_id').val();
            $.ajax({
                url: '<?= base_url('employees/update') ?>/' + id,
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
                        }).then(() => { loadEmployees(); });
                    } else {
                        if (res.errors) displayErrors('#editForm', res.errors);
                        else Swal.fire({ icon: 'error', title: 'Lỗi', text: res.message });
                    }
                }
            });
        });
    });

    function loadEmployees() {
        $.ajax({
            url: '<?= base_url('employees/list') ?>',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    employees = res.data;
                    renderTable(employees);
                } else {
                    $('#table-body').html('<tr><td colspan="7" class="text-center text-danger">Lỗi: ' + res.message + '</td></tr>');
                }
            }
        });
    }

    function renderTable(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="7" class="text-center text-secondary">Chưa có nhân viên nào.</td></tr>';
        } else {
            data.forEach(item => {
                const statusBadge = item.status === 'Hoạt động' 
                    ? '<span class="badge bg-success-lt">Hoạt động</span>' 
                    : '<span class="badge bg-danger-lt">Tạm khóa</span>';
                
                let roleColor = 'bg-blue-lt';
                if (item.role === 'Admin') roleColor = 'bg-red-lt';
                else if (item.role === 'Lãnh đạo') roleColor = 'bg-purple-lt';
                else if (item.role === 'Kế toán') roleColor = 'bg-green-lt';
                
                const shift = item.shift ? esc(item.shift) : '<span class="text-muted">-</span>';
                const lastLogin = item.last_login ? format_date(item.last_login) : '<span class="text-muted">Chưa đăng nhập</span>';

                html += `
                    <tr>
                        <td><strong>${esc(item.fullname)}</strong></td>
                        <td>${esc(item.username)}</td>
                        <td><span class="badge ${roleColor}">${esc(item.role)}</span></td>
                        <td>${shift}</td>
                        <td>${statusBadge}</td>
                        <td>${lastLogin}</td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <?php if (has_permission('employees.edit')) : ?>
                                <button class="btn btn-secondary btn-sm" onclick="openEditModal(${item.id})">
                                    <i class="ti ti-edit me-1"></i>Sửa
                                </button>
                                <?php endif; ?>
                                <?php if (has_permission('employees.delete')) : ?>
                                <button class="btn btn-danger btn-sm" onclick="confirmAction('<?= base_url('employees/delete') ?>/${item.id}', 'Xóa tài khoản?', 'Tài khoản nhân viên này sẽ bị xóa khỏi hệ thống.')">
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
    }

    function openEditModal(id) {
        const item = employees.find(e => e.id == id);
        if (!item) return;
        clearErrors('#editForm');
        $('#edit_id').val(item.id);
        $('#edit_username').val(item.username);
        $('#edit_password').val(''); // Keep blank
        $('#edit_fullname').val(item.fullname);
        $('#edit_role').val(item.role);
        $('#edit_shift').val(item.shift || '');
        $('#edit_status').val(item.status);
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
