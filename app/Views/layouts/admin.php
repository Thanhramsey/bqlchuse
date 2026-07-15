<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title><?= $this->renderSection('title') ?> - Hệ thống Quản lý Rác Thải</title>
    <meta name="X-CSRF-TOKEN" content="<?= csrf_hash() ?>"/>
    <!-- CSS files -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6fa;
        }
        .navbar-vertical {
            box-shadow: 2px 0 10px rgba(0,0,0,0.02);
        }
        .nav-item.active {
            font-weight: 600;
        }
        /* Sidebar Menu Styling overrides */
        .navbar-vertical .navbar-nav .nav-link {
            font-size: 0.95rem; 
            padding-top: 0.75rem; 
            padding-bottom: 0.75rem;
            letter-spacing: 0.02em;
        }
        .navbar-vertical .navbar-nav .nav-item {
            margin-bottom: 0.35rem; 
        }
        .navbar-vertical .navbar-nav .nav-link-icon {
            font-size: 1.25rem; 
            margin-right: 0.75rem;
        }
        .navbar-vertical .navbar-brand {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }
    </style>
    <?= $this->renderSection('styles') ?>
</head>
<body>
    <div class="page">
        <!-- Sidebar -->
        <aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand navbar-brand-autodark">
                    <a href="<?= base_url() ?>">
                        <i class="ti ti-trash text-success me-2 fs-1"></i>
                        <span class="font-weight-bold" style="color: #fff; font-size: 1.25rem;">BQL CÔNG TRÌNH ĐÔ THỊ CHƯ PƯH</span>
                    </a>
                </h1>
                <div class="navbar-nav flex-row d-lg-none">
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                            <span class="avatar avatar-sm bg-blue-lt"><?= strtoupper(substr(session('fullname'), 0, 1)) ?></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                            <a href="<?= base_url('logout') ?>" class="dropdown-item">Đăng xuất</a>
                        </div>
                    </div>
                </div>
                <div class="collapse navbar-collapse" id="sidebar-menu">
                    <ul class="navbar-nav pt-lg-3">
                        <!-- Dashboard -->
                        <?php if (has_permission('dashboard.view') && session('role') !== 'Nhân viên') : ?>
                        <li class="nav-item <?= (uri_string() === 'dashboard' || uri_string() === '') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= base_url('dashboard') ?>">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-dashboard"></i></span>
                                <span class="nav-link-title">Trang chủ</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Households -->
                        <?php if (has_permission('households.view')) : ?>
                        <li class="nav-item <?= str_contains(uri_string(), 'households') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= base_url('households') ?>">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-users"></i></span>
                                <span class="nav-link-title">Quản lý hộ dân</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Collection Routes -->
                        <?php if (has_permission('routes.view')) : ?>
                        <li class="nav-item <?= str_contains(uri_string(), 'routes') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= base_url('routes') ?>">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-map-2"></i></span>
                                <span class="nav-link-title">Tuyến thu gom</span>
                            </a>
                        </li>
                        <?php endif; ?>



                        <!-- Employees -->
                        <?php if (has_permission('employees.view')) : ?>
                        <li class="nav-item <?= str_contains(uri_string(), 'employees') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= base_url('employees') ?>">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-user-cog"></i></span>
                                <span class="nav-link-title">Nhân viên</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Fee Rates -->
                        <?php if (has_permission('fee_rates.view')) : ?>
                        <li class="nav-item <?= str_contains(uri_string(), 'fee-rates') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= base_url('fee-rates') ?>">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-coin"></i></span>
                                <span class="nav-link-title">Mức phí dịch vụ</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Payments -->
                        <?php if (has_permission('payments.view')) : ?>
                        <li class="nav-item <?= (str_contains(uri_string(), 'payments') && !str_contains(uri_string(), 'debts')) ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= base_url('payments') ?>">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-wallet"></i></span>
                                <span class="nav-link-title">Thu phí</span>
                            </a>
                        </li>
                        <?php endif; ?>



                        <!-- Reports -->
                        <?php if (has_permission('reports.view')) : ?>
                        <li class="nav-item <?= str_contains(uri_string(), 'reports') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= base_url('reports') ?>">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-report"></i></span>
                                <span class="nav-link-title">Báo cáo</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- System Logs -->
                        <?php if (has_permission('config.view')) : ?>
                        <li class="nav-item <?= str_contains(uri_string(), 'logs') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= base_url('logs') ?>">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-history"></i></span>
                                <span class="nav-link-title">Nhật ký hệ thống</span>
                            </a>
                        </li>
                        <?php endif; ?>

                        <!-- Config -->
                        <?php if (has_permission('config.view')) : ?>
                        <li class="nav-item <?= str_contains(uri_string(), 'config') ? 'active' : '' ?>">
                            <a class="nav-link" href="<?= base_url('config') ?>">
                                <span class="nav-link-icon d-md-none d-lg-inline-block"><i class="ti ti-settings"></i></span>
                                <span class="nav-link-title">Cấu hình</span>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </aside>

        <div class="page-wrapper">
            <!-- Header -->
            <header class="navbar navbar-expand-md navbar-light d-none d-lg-flex d-print-none">
                <div class="container-xl">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <div class="navbar-nav flex-row order-md-last">
                        <div class="nav-item dropdown">
                            <a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown">
                                <span class="avatar avatar-sm bg-blue-lt"><?= strtoupper(substr(session('fullname'), 0, 1)) ?></span>
                                <div class="d-none d-xl-block ps-2">
                                    <div><?= esc(session('fullname')) ?></div>
                                    <div class="mt-1 small text-muted"><?= esc(session('role')) ?></div>
                                </div>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
                                <a href="<?= base_url('logout') ?>" class="dropdown-item">Đăng xuất</a>
                            </div>
                        </div>
                    </div>
                    <div class="collapse navbar-collapse" id="navbar-menu">
                        <div>
                            <!-- Breadcrumbs Dynamic or Custom -->
                            <?= $this->renderSection('breadcrumb') ?>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <div class="page-body">
                <div class="container-xl">
                    <!-- Session Flash Alerts -->
                    <?php if (session()->has('error')) : ?>
                        <div class="alert alert-danger alert-dismissible" role="alert">
                            <div class="d-flex">
                                <div><i class="ti ti-alert-circle me-2"></i></div>
                                <div><?= esc(session('error')) ?></div>
                            </div>
                            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>
                    <?php endif; ?>
                    <?php if (session()->has('success')) : ?>
                        <div class="alert alert-success alert-dismissible" role="alert">
                            <div class="d-flex">
                                <div><i class="ti ti-check me-2"></i></div>
                                <div><?= esc(session('success')) ?></div>
                            </div>
                            <a class="btn-close" data-bs-dismiss="alert" aria-label="close"></a>
                        </div>
                    <?php endif; ?>

                    <?= $this->renderSection('content') ?>
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer footer-transparent d-print-none">
                <div class="container-xl">
                    <div class="row text-center align-items-center flex-row-reverse">
                        <div class="col-12 col-lg-auto mt-3 mt-lg-0">
                            <ul class="list-inline list-inline-dots mb-0">
                                <li class="list-inline-item">
                                    Bản quyền &copy; <?= date('Y') ?>
                                    <a href="." class="link-secondary">Ban Quản Lý Phường/Xã</a>.
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- JS Libs -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Automatic Inject CSRF token in jQuery AJAX requests -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="X-CSRF-TOKEN"]').attr('content') || '<?= csrf_hash() ?>'
            }
        });
        
        // General confirmation prompt with SweetAlert2
        function confirmAction(url, title = 'Bạn có chắc chắn?', text = 'Hành động này không thể hoàn tác!') {
            Swal.fire({
                title: title,
                text: text,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d63939',
                cancelButtonColor: '#909090',
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy bỏ'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: url,
                        method: 'POST',
                        dataType: 'json',
                        success: function(res) {
                            if (res.status) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Đã thực hiện!',
                                    text: res.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    window.location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Thất bại',
                                    text: res.message
                                });
                            }
                        },
                        error: function(xhr) {
                            let msg = 'Lỗi kết nối máy chủ.';
                            if (xhr.status === 430) {
                                msg = xhr.responseJSON.message;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: msg
                            });
                        }
                    });
                }
            });
        }
    </script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
