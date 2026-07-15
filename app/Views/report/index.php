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
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Báo cáo doanh thu vệ sinh môi trường</h3>
                <div class="card-options btn-list">
                    <a href="<?= base_url('reports/export/excel') ?>" class="btn btn-success">
                        <i class="ti ti-file-spreadsheet me-1"></i>Xuất Excel
                    </a>
                    <a href="<?= base_url('reports/export/pdf') ?>" target="_blank" class="btn btn-danger">
                        <i class="ti ti-file-description me-1"></i>In báo cáo (PDF)
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Nav tabs -->
                <ul class="nav nav-tabs" data-bs-toggle="tabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <a href="#tab-month" class="nav-link active" data-bs-toggle="tab" role="tab" aria-selected="true">
                            <i class="ti ti-calendar me-1"></i>Theo tháng
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#tab-type" class="nav-link" data-bs-toggle="tab" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ti ti-users me-1"></i>Theo loại hộ
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#tab-group" class="nav-link" data-bs-toggle="tab" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ti ti-map-pin me-1"></i>Theo tổ dân phố
                        </a>
                    </li>
                    <li class="nav-item" role="presentation">
                        <a href="#tab-user" class="nav-link" data-bs-toggle="tab" role="tab" aria-selected="false" tabindex="-1">
                            <i class="ti ti-user me-1"></i>Theo nhân viên
                        </a>
                    </li>
                </ul>
                
                <div class="tab-content pt-3">
                    <!-- Tab Month -->
                    <div class="tab-pane active show" id="tab-month" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Tháng thu</th>
                                        <th>Số hóa đơn đã nộp</th>
                                        <th>Tổng tiền nộp (VND)</th>
                                    </tr>
                                </thead>
                                <tbody id="body-month">
                                    <tr><td colspan="3" class="text-center">Đang tải...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Type -->
                    <div class="tab-pane" id="tab-type" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Loại hộ dân</th>
                                        <th>Số hóa đơn đã nộp</th>
                                        <th>Tổng tiền nộp (VND)</th>
                                    </tr>
                                </thead>
                                <tbody id="body-type">
                                    <tr><td colspan="3" class="text-center">Đang tải...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab Group -->
                    <div class="tab-pane" id="tab-group" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Tổ dân phố</th>
                                        <th>Số hóa đơn đã nộp</th>
                                        <th>Tổng tiền nộp (VND)</th>
                                    </tr>
                                </thead>
                                <tbody id="body-group">
                                    <tr><td colspan="3" class="text-center">Đang tải...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab User -->
                    <div class="tab-pane" id="tab-user" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">
                                <thead>
                                    <tr>
                                        <th>Nhân viên thu ngân</th>
                                        <th>Số hóa đơn đã nộp</th>
                                        <th>Tổng tiền nộp (VND)</th>
                                    </tr>
                                </thead>
                                <tbody id="body-user">
                                    <tr><td colspan="3" class="text-center">Đang tải...</td></tr>
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
        $.ajax({
            url: '<?= base_url('reports/revenue') ?>',
            method: 'GET',
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    renderSummaries(res.data);
                }
            }
        });
    });

    function renderSummaries(data) {
        // Month Table
        let htmlMonth = '';
        if (data.by_month.length === 0) {
            htmlMonth = '<tr><td colspan="3" class="text-center text-muted">Chưa có dữ liệu thanh toán nào.</td></tr>';
        } else {
            data.by_month.forEach(row => {
                htmlMonth += `<tr><td><strong>${esc(row.group_key)}</strong></td><td>${row.bills_count}</td><td class="text-blue font-weight-medium">${format_money(row.total_amount)}</td></tr>`;
            });
        }
        $('#body-month').html(htmlMonth);

        // Type Table
        let htmlType = '';
        if (data.by_household_type.length === 0) {
            htmlType = '<tr><td colspan="3" class="text-center text-muted">Chưa có dữ liệu.</td></tr>';
        } else {
            data.by_household_type.forEach(row => {
                htmlType += `<tr><td><strong>${esc(row.group_key)}</strong></td><td>${row.bills_count}</td><td class="text-blue font-weight-medium">${format_money(row.total_amount)}</td></tr>`;
            });
        }
        $('#body-type').html(htmlType);

        // Group Table
        let htmlGroup = '';
        if (data.by_ward_group.length === 0) {
            htmlGroup = '<tr><td colspan="3" class="text-center text-muted">Chưa có dữ liệu.</td></tr>';
        } else {
            data.by_ward_group.forEach(row => {
                htmlGroup += `<tr><td><strong>${esc(row.group_key)}</strong></td><td>${row.bills_count}</td><td class="text-blue font-weight-medium">${format_money(row.total_amount)}</td></tr>`;
            });
        }
        $('#body-group').html(htmlGroup);

        // User Table
        let htmlUser = '';
        if (data.by_collector.length === 0) {
            htmlUser = '<tr><td colspan="3" class="text-center text-muted">Chưa có dữ liệu.</td></tr>';
        } else {
            data.by_collector.forEach(row => {
                htmlUser += `<tr><td><strong>${esc(row.group_key)}</strong></td><td>${row.bills_count}</td><td class="text-blue font-weight-medium">${format_money(row.total_amount)}</td></tr>`;
            });
        }
        $('#body-user').html(htmlUser);
    }

    function format_money(amount) {
        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
    }

    function esc(string) {
        if (!string) return '';
        const map = {'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'};
        return string.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
</script>
<?= $this->endSection() ?>
