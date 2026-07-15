<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Quản lý công nợ<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<ol class="breadcrumb breadcrumb-arrows" aria-label="breadcrumbs">
    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Trang chủ</a></li>
    <li class="breadcrumb-item active" aria-current="page"><a href="#">Công nợ</a></li>
</ol>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">Danh sách hộ dân chưa nộp phí (Nợ xấu / Trễ hạn)</h3>
                <div class="card-options">
                    <span class="badge bg-danger-lt" id="debt_total_badge">Đang tính toán...</span>
                </div>
            </div>
            <div class="table-responsive p-3">
                <table id="debtsTable" class="table table-striped table-hover card-table table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th>Kỳ nợ</th>
                            <th>Mã hộ</th>
                            <th>Tên chủ hộ</th>
                            <th>Số điện thoại</th>
                            <th>Địa chỉ</th>
                            <th>Số tiền nợ</th>
                            <th>Trạng thái</th>
                            <th class="w-1">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="spinner-border text-primary me-2" role="status"></div> Đang tải dữ liệu công nợ...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    $(document).ready(function() {
        loadDebts();
    });

    function loadDebts() {
        $.ajax({
            url: '<?= base_url('payments/list') ?>',
            method: 'GET',
            data: { status: ['Chưa thanh toán', 'Trễ hạn'] }, // Both unpaid statuses
            dataType: 'json',
            success: function(res) {
                if (res.status) {
                    renderTable(res.data);
                } else {
                    $('#table-body').html('<tr><td colspan="8" class="text-center text-danger">Lỗi tải dữ liệu.</td></tr>');
                }
            }
        });
    }

    function renderTable(data) {
        let html = '';
        let totalDebtSum = 0;
        
        if (data.length === 0) {
            html = '<tr><td colspan="8" class="text-center text-secondary">Tuyệt vời! Hiện tại không có công nợ tồn đọng nào.</td></tr>';
            $('#debt_total_badge').removeClass('bg-danger-lt').addClass('bg-success-lt').text('Không có nợ');
        } else {
            data.forEach(item => {
                totalDebtSum += parseFloat(item.amount);
                
                const statusBadge = item.payment_status === 'Trễ hạn' 
                    ? '<span class="badge bg-danger-lt">Trễ hạn</span>' 
                    : '<span class="badge bg-warning-lt">Chưa nộp</span>';
                
                const phone = item.phone ? esc(item.phone) : '-';

                html += `
                    <tr>
                        <td><strong>${esc(item.billing_month)}</strong></td>
                        <td><span class="text-secondary">${esc(item.household_code)}</span></td>
                        <td><strong>${esc(item.owner_name)}</strong></td>
                        <td>${phone}</td>
                        <td>${esc(item.address)}</td>
                        <td><span class="text-danger font-weight-semibold">${format_money(item.amount)}</span></td>
                        <td>${statusBadge}</td>
                        <td>
                            <div class="btn-list flex-nowrap">
                                <?php if (has_permission('debts.remind')) : ?>
                                <button class="btn btn-warning btn-sm" onclick="sendReminder(${item.id})">
                                    <i class="ti ti-bell me-1"></i>Nhắc thu nợ
                                </button>
                                <?php endif; ?>
                                <a href="<?= base_url('payments') ?>" class="btn btn-primary btn-sm">
                                    <i class="ti ti-cash me-1"></i>Thu phí
                                </a>
                            </div>
                        </td>
                    </tr>
                `;
            });
            $('#debt_total_badge').removeClass('bg-success-lt').addClass('bg-danger-lt')
                .text(`Tổng nợ: ` + format_money(totalDebtSum));
        }
        $('#table-body').html(html);
    }

    function sendReminder(id) {
        Swal.fire({
            title: 'Gửi nhắc nhở?',
            text: 'Hệ thống sẽ gửi thông báo SMS & Email nhắc thu tiền phí đến chủ hộ này.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59f00',
            confirmButtonText: 'Gửi ngay',
            cancelButtonText: 'Đóng'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('debts/remind') ?>/' + id,
                    method: 'POST',
                    data: { <?= csrf_token() ?>: '<?= csrf_hash() ?>' },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Đã gửi!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            Swal.fire({ icon: 'error', title: 'Lỗi', text: res.message });
                        }
                    }
                });
            }
        });
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
