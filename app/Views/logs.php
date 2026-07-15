<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Nhật ký hệ thống<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<ol class="breadcrumb breadcrumb-arrows" aria-label="breadcrumbs">
    <li class="breadcrumb-item"><a href="<?= base_url('dashboard') ?>">Trang chủ</a></li>
    <li class="breadcrumb-item active" aria-current="page"><a href="#">Nhật ký hệ thống</a></li>
</ol>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="row row-cards">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Nhật ký hoạt động kiểm toán hệ thống</h3>
            </div>
            <div class="table-responsive p-3">
                <table id="logsTable" class="table table-striped table-hover card-table table-vcenter text-nowrap">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Hành động</th>
                            <th>Phân hệ</th>
                            <th>Mô tả chi tiết</th>
                            <th>Tài khoản thực hiện</th>
                            <th>Địa chỉ IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)) : ?>
                            <tr>
                                <td colspan="6" class="text-center text-secondary py-4">Chưa ghi nhận hoạt động nào.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($logs as $log) : ?>
                                <tr>
                                    <td><?= format_date($log['created_at']) ?></td>
                                    <td><span class="badge bg-secondary-lt"><?= esc($log['action']) ?></span></td>
                                    <td><strong><?= esc($log['module']) ?></strong></td>
                                    <td><?= esc($log['description']) ?></td>
                                    <td><strong><?= esc($log['fullname'] ?? 'Hệ thống') ?></strong></td>
                                    <td><code><?= esc($log['ip_address']) ?></code></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Datatables can be initiated if required, or simple scroll -->
<?= $this->endSection() ?>
