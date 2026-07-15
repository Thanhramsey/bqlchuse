<!doctype html>
<html lang="vi">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Đăng nhập - Hệ thống Quản lý Rác Thải</title>
    <!-- CSS files -->
    <link href="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/css/tabler.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" rel="stylesheet"/>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f4f6fa;
        }
        .card-md {
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>
<body class="d-flex flex-column">
    <div class="page page-center">
        <div class="container container-tight py-4">
            <div class="text-center mb-4">
                <a href="." class="navbar-brand navbar-brand-autodark">
                    <span class="fs-2 fw-bold text-primary">
                        <i class="ti ti-trash text-success me-2 fs-1"></i>BQL CÔNG TRÌNH ĐÔ THỊ CHƯ PƯH
                    </span>
                </a>
            </div>
            <div class="card card-md">
                <div class="card-body">
                    <h2 class="h2 text-center mb-4">Đăng nhập hệ thống</h2>
                    <form id="loginForm" autocomplete="off" novalidate>
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label">Tên tài khoản</label>
                            <input type="text" name="username" class="form-control" placeholder="Nhập tài khoản" autocomplete="off">
                            <div class="invalid-feedback" id="err_username"></div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">
                                Mật khẩu
                            </label>
                            <div class="input-group input-group-flat">
                                <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu" autocomplete="off">
                                <span class="input-group-text">
                                    <a href="#" class="link-secondary" title="Hiển thị mật khẩu" data-bs-toggle="tooltip" id="togglePassword">
                                        <i class="ti ti-eye"></i>
                                    </a>
                                </span>
                                <div class="invalid-feedback" id="err_password"></div>
                            </div>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary w-100 fw-medium btn-login" style="background-color: #206bc4;">
                                <span class="spinner-border spinner-border-sm me-2 d-none" role="status"></span>
                                Đăng nhập
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="text-center text-secondary mt-3">
                Hệ thống Quản lý Rác Thải Ban Quản Lý Phường/Xã v1.0.0
            </div>
        </div>
    </div>

    <!-- JS Libs -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@1.0.0-beta20/dist/js/tabler.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        $(document).ready(function() {
            // Password toggle
            $('#togglePassword').on('click', function(e) {
                e.preventDefault();
                const passInput = $('input[name="password"]');
                const icon = $(this).find('i');
                if (passInput.attr('type') === 'password') {
                    passInput.attr('type', 'text');
                    icon.removeClass('ti-eye').addClass('ti-eye-off');
                } else {
                    passInput.attr('type', 'password');
                    icon.removeClass('ti-eye-off').addClass('ti-eye');
                }
            });

            // Form Submit
            $('#loginForm').on('submit', function(e) {
                e.preventDefault();
                
                // Clear errors
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
                
                const submitBtn = $('.btn-login');
                const spinner = submitBtn.find('.spinner-border');
                
                submitBtn.prop('disabled', true);
                spinner.removeClass('d-none');
                
                $.ajax({
                    url: '<?= base_url('login') ?>',
                    method: 'POST',
                    data: $(this).serialize(),
                    dataType: 'json',
                    success: function(res) {
                        submitBtn.prop('disabled', false);
                        spinner.addClass('d-none');
                        
                        if (res.status) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.href = res.data.redirect;
                            });
                        } else {
                            if (res.errors) {
                                // Display field validation errors
                                Object.keys(res.errors).forEach(key => {
                                    const input = $('[name="' + key + '"]');
                                    input.addClass('is-invalid');
                                    $('#err_' + key).text(res.errors[key]);
                                });
                            }
                            if (res.message && !res.errors.username && !res.errors.password) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Thất bại',
                                    text: res.message
                                });
                            }
                        }
                    },
                    error: function() {
                        submitBtn.prop('disabled', false);
                        spinner.addClass('d-none');
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi hệ thống',
                            text: 'Không thể kết nối đến máy chủ. Vui lòng thử lại.'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
