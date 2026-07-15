<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Quản lý mức phí dịch vụ<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<ol class="breadcrumb breadcrumb-arrows" aria-label="breadcrumbs">
    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Trang chủ</a></li>
    <li class="breadcrumb-item active" aria-current="page"><a href="#">Mức phí dịch vụ</a></li>
</ol>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Cấu hình đơn giá thu gom rác</h3>
                <div class="card-options">
                    <?php if (has_permission('fee_rates.create')) : ?>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal-add">
                        <i class="ti ti-plus me-2"></i>Thêm mức phí
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="table-responsive p-3">
                <table id="feeRatesTable" class="table table-striped table-hover card-table table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Loại hộ dân</th>
                            <th>Đơn giá / Tháng</th>
                            <th>Thuế VAT (%)</th>
                            <th>Ngày hiệu lực</th>
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
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Thêm mới đơn giá mức phí</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addForm">
                <?= csrf_field() ?>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Loại hộ dân</label>
                        <input type="text" name="household_type" class="form-control" placeholder="Ví dụ: Hộ kinh doanh ăn uống">
                        <div class="invalid-feedback err_household_type"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Đơn giá (VNĐ / Tháng)</label>
                        <input type="number" name="price" class="form-control" placeholder="Ví dụ: 120000">
                        <div class="invalid-feedback err_price"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Thuế VAT (%)</label>
                        <input type="number" name="vat" class="form-control" value="10" placeholder="Ví dụ: 10">
                        <div class="invalid-feedback err_vat"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Ngày áp dụng hiệu lực</label>
                        <input type="date" name="effective_date" class="form-control" value="<?= date('Y-m-d') ?>">
                        <div class="invalid-feedback err_effective_date"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Trạng thái</label>
                        <select name="status" class="form-select">
                            <option value="Đang hiệu lực">Đang hiệu lực</option>
                            <option value="Hết hiệu lực">Hết hiệu lực</option>
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
                <h5 class="modal-title">Sửa mức phí dịch vụ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editForm">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label required">Loại hộ dân</label>
                        <input type="text" name="household_type" id="edit_household_type" class="form-control">
                        <div class="invalid-feedback err_household_type"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Đơn giá (VNĐ / Tháng)</label>
                        <input type="number" name="price" id="edit_price" class="form-control">
                        <div class="invalid-feedback err_price"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Thuế VAT (%)</label>
                        <input type="number" name="vat" id="edit_vat" class="form-control">
                        <div class="invalid-feedback err_vat"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Ngày áp dụng hiệu lực</label>
                        <input type="date" name="effective_date" id="edit_effective_date" class="form-control">
                        <div class="invalid-feedback err_effective_date"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label required">Trạng thái</label>
                        <select name="status" id="edit_status" class="form-select">
                            <option value="Đang hiệu lực">Đang hiệu lực</option>
                            <option value="Hết hiệu lực">Hết hiệu lực</option>
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
    let feeRates = [];

    $(document).ready(function() {
        loadFeeRates();

        // Add
        $('#addForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors('#addForm');
            $.ajax({
                url: '<?= base_url('fee-rates/create') ?>',
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
                        }).then(() => { loadFeeRates(); });
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
                url: '<?= base_url('fee-rates/update') ?>/' + id,
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
                        }).then(() => { loadFeeRates(); });
                    } else {
                        if (res.errors) displayErrors('#editForm', res.errors);
                        else Swal.fire({ icon: 'error', title: 'Lỗi', text: res.message });
                    }
                }
            });
        });
    });

    function loadFeeRates() {
        $.ajax({
            url: '<?= base_url('fee-rates/list') ?>',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    feeRates = res.data;
                    renderTable(feeRates);
                } else {
                    $('#table-body').html('<tr><td colspan="7" class="text-center text-danger">Lỗi: ' + res.message + '</td></tr>');
                }
            }
        });
    }

    function renderTable(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="7" class="text-center text-secondary">Chưa cấu hình mức phí nào.</td></tr>';
        } else {
            data.forEach(item => {
                const statusBadge = item.status === 'Đang hiệu lực' 
                    ? '<span class="badge bg-success-lt">Đang hiệu lực</span>' 
                    : '<span class="badge bg-secondary-lt">Hết hiệu lực</span>';
                
                const formattedPrice = format_money(item.price);
                const formattedDate = format_date(item.effective_date);

                html += `
                    <tr>
                        <td>${item.id}</td>
                        <td><strong>${esc(item.household_type)}</strong></td>
                        <td><span class="text-blue font-weight-medium">${formattedPrice}</span></td>
                        <td><span class="badge bg-purple-lt">${parseFloat(item.vat)}%</span></td>
                        <td>${formattedDate}</td>
                        <td>${statusBadge}</td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <?php if (has_permission('fee_rates.edit')) : ?>
                                <button class="btn btn-secondary btn-sm" onclick="openEditModal(${item.id})">
                                    <i class="ti ti-edit me-1"></i>Sửa
                                </button>
                                <?php endif; ?>
                                <?php if (has_permission('fee_rates.delete')) : ?>
                                <button class="btn btn-danger btn-sm" onclick="confirmAction('<?= base_url('fee-rates/delete') ?>/${item.id}', 'Xóa mức phí?', 'Hành động này sẽ xóa cấu hình mức phí.')">
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
        const item = feeRates.find(fr => fr.id == id);
        if (!item) return;
        clearErrors('#editForm');
        $('#edit_id').val(item.id);
        $('#edit_household_type').val(item.household_type);
        // Remove decimal portion for input box if integer
        $('#edit_price').val(parseInt(item.price));
        $('#edit_vat').val(parseInt(item.vat));
        $('#edit_effective_date').val(item.effective_date);
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

    function format_money(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }

    function format_date(dateStr) {
        if (!dateStr) return '-';
        const d = new Date(dateStr);
        return d.toLocaleDateString('vi-VN');
    }

    function esc(string) {
        if (!string) return '';
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return string.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
</script>
<?= $this->endSection() ?>
