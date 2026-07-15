<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Cấu hình hệ thống<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<ol class="breadcrumb breadcrumb-arrows" aria-label="breadcrumbs">
    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Trang chủ</a></li>
    <li class="breadcrumb-item active" aria-current="page"><a href="#">Cấu hình hệ thống</a></li>
</ol>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row row-cards">
    <div class="col-md-8">
        <form id="configForm" class="card">
            <?= csrf_field() ?>
            <div class="card-header">
                <h3 class="card-title">Cấu hình thông tin ban quản lý và thanh toán</h3>
            </div>
            <div class="card-body">
                <h3 class="card-title text-blue mb-3"><i class="ti ti-building me-2"></i>Thông tin đơn vị</h3>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label required">Tên đơn vị quản lý</label>
                        <input type="text" name="company_name" class="form-control" value="<?= esc($config['company_name']) ?>">
                        <div class="invalid-feedback err_company_name"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Số điện thoại liên hệ</label>
                        <input type="text" name="company_phone" class="form-control" value="<?= esc($config['company_phone']) ?>">
                        <div class="invalid-feedback err_company_phone"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Email liên hệ</label>
                        <input type="email" name="company_email" class="form-control" value="<?= esc($config['company_email']) ?>">
                        <div class="invalid-feedback err_company_email"></div>
                    </div>
                </div>

                <hr class="my-4">
                <h3 class="card-title text-blue mb-3"><i class="ti ti-file-invoice me-2"></i>Cấu hình Hóa đơn Điện tử VNPT</h3>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label required">Publish Service Address</label>
                        <input type="text" name="PUBLISH_SERVICE_ADDRESS_ID" class="form-control" value="<?= esc($config['PUBLISH_SERVICE_ADDRESS_ID'] ?? '') ?>">
                        <div class="invalid-feedback err_PUBLISH_SERVICE_ADDRESS_ID"></div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label required">Business Service Address</label>
                        <input type="text" name="BUSINESS_SERVICE_ADDRESS_ID" class="form-control" value="<?= esc($config['BUSINESS_SERVICE_ADDRESS_ID'] ?? '') ?>">
                        <div class="invalid-feedback err_BUSINESS_SERVICE_ADDRESS_ID"></div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <label class="form-label required">Portal Service Address</label>
                        <input type="text" name="PORTAL_SERVICE_ADDRESS_ID" class="form-control" value="<?= esc($config['PORTAL_SERVICE_ADDRESS_ID'] ?? '') ?>">
                        <div class="invalid-feedback err_PORTAL_SERVICE_ADDRESS_ID"></div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Webservice Username (WS User)</label>
                        <input type="text" name="WS_USER_ID" class="form-control" value="<?= esc($config['WS_USER_ID'] ?? '') ?>">
                        <div class="invalid-feedback err_WS_USER_ID"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Webservice Password (WS Password)</label>
                        <input type="text" name="WS_PASSWORD_ID" class="form-control" value="<?= esc($config['WS_PASSWORD_ID'] ?? '') ?>">
                        <div class="invalid-feedback err_WS_PASSWORD_ID"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Customer Username (C User)</label>
                        <input type="text" name="C_USER_ID" class="form-control" value="<?= esc($config['C_USER_ID'] ?? '') ?>">
                        <div class="invalid-feedback err_C_USER_ID"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Customer Password (C Password)</label>
                        <input type="text" name="C_PASSWORD_ID" class="form-control" value="<?= esc($config['C_PASSWORD_ID'] ?? '') ?>">
                        <div class="invalid-feedback err_C_PASSWORD_ID"></div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Mẫu số hóa đơn (Pattern)</label>
                        <input type="text" name="PATTERN_HD_ID" class="form-control" value="<?= esc($config['PATTERN_HD_ID'] ?? '') ?>" placeholder="Ví dụ: 1/003">
                        <div class="invalid-feedback err_PATTERN_HD_ID"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label required">Ký hiệu hóa đơn (Serial)</label>
                        <input type="text" name="SERIAL_HD_ID" class="form-control" value="<?= esc($config['SERIAL_HD_ID'] ?? '') ?>" placeholder="Ví dụ: C23TAA">
                        <div class="invalid-feedback err_SERIAL_HD_ID"></div>
                    </div>
                </div>

                <!-- QR configurations hidden for now -->
                <div style="display: none !important;">
                    <hr class="my-4">
                    <h3 class="card-title text-blue mb-3"><i class="ti ti-qrcode me-2"></i>Cấu hình thanh toán VietQR</h3>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Ngân hàng thụ hưởng</label>
                            <select name="bank_id" class="form-select">
                                <option value="vietinbank" <?= $config['bank_id'] === 'vietinbank' ? 'selected' : '' ?>>VietinBank (ICB)</option>
                                <option value="mbbank" <?= $config['bank_id'] === 'mbbank' ? 'selected' : '' ?>>MBBank</option>
                                <option value="vcb" <?= $config['bank_id'] === 'vcb' ? 'selected' : '' ?>>Vietcombank</option>
                                <option value="bidv" <?= $config['bank_id'] === 'bidv' ? 'selected' : '' ?>>BIDV</option>
                            </select>
                            <div class="invalid-feedback err_bank_id"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label required">Số tài khoản ngân hàng</label>
                            <input type="text" name="bank_account" class="form-control" value="<?= esc($config['bank_account']) ?>">
                            <div class="invalid-feedback err_bank_account"></div>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label required">Tên chủ tài khoản (Viết hoa không dấu)</label>
                            <input type="text" name="bank_name" class="form-control" value="<?= esc($config['bank_name']) ?>" placeholder="Ví dụ: BQL PHUONG NGUYEN DU">
                            <div class="invalid-feedback err_bank_name"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer text-end">
                <button type="submit" class="btn btn-primary" id="btn-save-config">Lưu cấu hình</button>
            </div>
        </form>
    </div>

    <!-- Info box -->
    <div class="col-md-4">
        <div class="card bg-primary-lt">
            <div class="card-body">
                <h3 class="card-title text-primary"><i class="ti ti-help-circle me-1"></i>Hướng dẫn cấu hình</h3>
                <p class="text-secondary small">
                    - Thông tin đơn vị quản lý sẽ được in ra ở phần tiêu đề hóa đơn / biên lai thanh toán của hộ dân.
                </p>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        $('#configForm').on('submit', function(e) {
            e.preventDefault();
            clearErrors();
            
            const btn = $('#btn-save-config');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Đang xử lý...');

            $.ajax({
                url: '<?= base_url('config/update') ?>',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(res) {
                    btn.prop('disabled', false).text('Lưu cấu hình');
                    if (res.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Thành công',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        if (res.errors) displayErrors(res.errors);
                        else Swal.fire({ icon: 'error', title: 'Lỗi', text: res.message });
                    }
                }
            });
        });
    });

    function displayErrors(errors) {
        Object.keys(errors).forEach(field => {
            const input = $('[name="' + field + '"]');
            input.addClass('is-invalid');
            $('.err_' + field).text(errors[field]);
        });
    }

    function clearErrors() {
        $('.form-control, .form-select').removeClass('is-invalid');
        $('.invalid-feedback').text('');
    }
</script>
<?= $this->endSection() ?>
