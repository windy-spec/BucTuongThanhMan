<?php
    session_start();
    include_once(__DIR__ . '/../config.php');
    
    // 1. Xử lý LỖI (Mật khẩu sai)
    $message = $_SESSION['message'] ?? '';
    $type = (strpos($message, 'Sai') !== false || strpos($message, 'Lỗi') !== false) ? 'error' : 'success';
    unset($_SESSION['message']);

    // 2. Xử lý THÀNH CÔNG (Redirect)
    $redirect_message = $_SESSION['swal_message'] ?? '';
    $redirect_target = $_SESSION['swal_target'] ?? '';
    unset($_SESSION['swal_message']);
    unset($_SESSION['swal_target']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng nhập - Hệ thống Quản lý</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        body { background-color: #f8f9fa; }
        .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { width: 100%; max-width: 450px; border: none; border-radius: 0.75rem; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="card shadow-lg login-card">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fa fa-user-circle fa-3x text-primary"></i>
                    <h2 class="mt-3 fw-bold">Đăng nhập</h2>
                    <p class="text-muted">Chào mừng trở lại!</p>
                </div>
                
                <form action="login_process.php" method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Tên đăng nhập:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <div class="invalid-feedback">Vui lòng nhập Tên đăng nhập.</div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">Mật khẩu:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback">Vui lòng nhập Mật khẩu.</div>

                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                <i class="fa fa-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" value="" id="rememberMe">
                            <label class="form-check-label" for="rememberMe">Ghi nhớ tôi</label>
                        </div>
                        <a href="forgot.php" class="small text-decoration-none">Quên mật khẩu?</a> 
                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg fw-bold">
                            <i class="fa fa-sign-in-alt"></i> Đăng nhập
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                <div class="text-center">
                    <p class="text-muted mb-0">Chưa có tài khoản?</p>
                    <a href="register.php" class="fw-bold text-decoration-none">
                        Đăng ký tại đây
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // HÀM TOGGLE
        function setupPasswordToggle(toggleBtnId, inputId, iconId) {
            const toggleButton = document.getElementById(toggleBtnId);
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);

            if (toggleButton) { // Ngăn chặn crash nếu ID không tồn tại
                toggleButton.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    if (type === 'text') {
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                    } else {
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                    }
                });
            }
        }

        (function () {
          'use strict'
          const form = document.querySelector('.needs-validation');
          
          // Kích hoạt Toggle
          setupPasswordToggle('togglePassword', 'password', 'eyeIcon'); 

          // Kích hoạt Validation
          form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add('was-validated');
          }, false);
        })()
    </script>

    <?php if ($message || $redirect_message): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $redirect_message ? "success" : $type; ?>',
            title: 'Thông báo',
            html: '<?php echo str_replace(["'", "\n"], ["\'", "<br>"], $redirect_message ? $redirect_message : $message); ?>',
            showConfirmButton: false, 
            timer: 2000
        }).then((result) => {
            <?php if ($redirect_target): ?>
            // Chuyển hướng sau khi SweetAlert đóng
            window.location.href = '<?php echo $redirect_target; ?>';
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>
</body>
</html>