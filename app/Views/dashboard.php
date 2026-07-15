<?= $this->extend('layouts/admin') ?>

<?= $this->section('title') ?>Bảng điều khiển<?= $this->endSection() ?>

<?= $this->section('breadcrumb') ?>
<ol class="breadcrumb breadcrumb-arrows" aria-label="breadcrumbs">
    <li class="breadcrumb-item active" aria-current="page"><a href="#">Trang chủ</a></li>
</ol>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Alert info for developer -->
<div class="row row-cards mb-3">
    <div class="col-12">
        <div class="alert alert-important alert-info alert-dismissible" role="alert">
            <div class="d-flex">
                <div><i class="ti ti-info-circle me-2"></i></div>
                <div>Chào mừng bạn đến với hệ thống Quản lý Rác Thải. Bạn đang đăng nhập với quyền <strong><?= esc(session('role')) ?></strong>.</div>
            </div>
            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
        </div>
    </div>
</div>

<!-- KPI Cards -->
<div class="row row-cards">
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-primary text-white avatar"><i class="ti ti-users"></i></span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            <?= number_format($metrics['total_households']) ?> Hộ dân
                        </div>
                        <div class="text-secondary">Đang thu gom hoạt động</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-success text-white avatar"><i class="ti ti-currency-dollar"></i></span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            <?= format_money($metrics['total_revenue']) ?>
                        </div>
                        <div class="text-secondary">Doanh thu thu được</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-danger text-white avatar"><i class="ti ti-alert-triangle"></i></span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            <?= format_money($metrics['total_pending']) ?>
                        </div>
                        <div class="text-secondary">Tiền chưa thu (Nợ tích lũy)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card card-sm">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-auto">
                        <span class="bg-twitter text-white avatar"><i class="ti ti-user-cog"></i></span>
                    </div>
                    <div class="col">
                        <div class="font-weight-medium">
                            <?= number_format($metrics['total_employees']) ?> Nhân sự
                        </div>
                        <div class="text-secondary">Nhân sự ban quản lý</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row row-cards mt-3">
    <!-- Line Chart (Revenue Trend) -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Biểu đồ doanh thu thu phí năm <?= date('Y') ?></h3>
                <div id="chart-revenue-trends" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>

    <!-- Pie Chart (Household Composition) -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-body">
                <h3 class="card-title">Cơ cấu hộ dân theo loại</h3>
                <div id="chart-household-types" style="min-height: 350px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Logs & Notifications Row -->
<div class="row row-cards mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Nhật ký hoạt động hệ thống gần đây</h3>
            </div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php if (empty($recentLogs)) : ?>
                        <div class="list-group-item text-center text-muted">Chưa có nhật ký hoạt động nào.</div>
                    <?php else : ?>
                        <?php foreach ($recentLogs as $log) : ?>
                            <div class="list-group-item">
                                <div class="row align-items-center">
                                    <div class="col-auto">
                                        <span class="badge bg-secondary-lt"><?= esc($log['action']) ?></span>
                                    </div>
                                    <div class="col">
                                        <div class="font-weight-medium"><?= esc($log['description']) ?></div>
                                        <div class="small text-secondary">
                                            Module: <?= esc($log['module']) ?> | Thực hiện bởi: <?= esc($log['fullname'] ?? 'Hệ thống') ?> | IP: <?= esc($log['ip_address']) ?>
                                        </div>
                                    </div>
                                    <div class="col-auto text-secondary small">
                                        <?= format_date($log['created_at']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- ApexCharts JS -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    $(document).ready(function() {
        // Line chart for monthly revenue
        const months = <?= json_encode($chartData['months']) ?>;
        const revenueTotals = <?= json_encode($chartData['totals']) ?>;

        const optionsRevenue = {
            chart: {
                type: 'area',
                height: 350,
                toolbar: { show: false }
            },
            dataLabels: { enabled: false },
            stroke: { curve: 'smooth', width: 2 },
            series: [{
                name: 'Doanh thu thu phí',
                data: revenueTotals
            }],
            xaxis: {
                categories: months
            },
            colors: ['#206bc4'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.3,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
                    }
                }
            }
        };

        const chartRevenue = new ApexCharts(document.querySelector("#chart-revenue-trends"), optionsRevenue);
        chartRevenue.render();

        // Pie/Donut chart for households stats
        const typeLabels = <?= json_encode($stats['labels']) ?>;
        const typeCounts = <?= json_encode($stats['counts']) ?>;

        const optionsHouseholds = {
            chart: {
                type: 'donut',
                height: 320
            },
            labels: typeLabels,
            series: typeCounts,
            colors: ['#206bc4', '#2fb344', '#f59f00', '#4299e1'],
            legend: {
                position: 'bottom'
            },
            dataLabels: {
                enabled: true
            }
        };

        const chartHouseholds = new ApexCharts(document.querySelector("#chart-household-types"), optionsHouseholds);
        chartHouseholds.render();
    });
</script>
<?= $this->endSection() ?>
