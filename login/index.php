<?php
    session_start();
    include_once(__DIR__ . '/../config.php');
    
    // 1. BẮT LINK TỪ URL (DO FILE ROOMS.PHP GỬI SANG)
    // Biến này sẽ được điền vào ô input ẩn bên dưới
    $redirect_url = "";
    if (isset($_GET['redirect'])) {
        $redirect_url = $_GET['redirect'];
        // Lưu dự phòng vào session
        $_SESSION['redirect_after_login'] = $_GET['redirect'];
    } elseif (isset($_SESSION['redirect_after_login'])) {
        $redirect_url = $_SESSION['redirect_after_login'];
    }

    // 2. Xử lý thông báo lỗi/thành công
    $message = $_SESSION['message'] ?? '';
    $type = (strpos($message, 'Sai') !== false || strpos($message, 'Lỗi') !== false) ? 'error' : 'success';
    unset($_SESSION['message']);

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
        body { 
            background-image: url('../assets/img/background.jpg'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-color: rgba(0, 0, 0, 0.3);
            background-blend-mode: darken;
        }
        .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 20px; }
        .login-card { width: 100%; max-width: 450px; border: none; border-radius: 0.75rem; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4); }
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
                    
                    <input type="hidden" name="redirect_custom" value="<?php echo htmlspecialchars($redirect_url); ?>">
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
                    <a href="register.php" class="fw-bold text-decoration-none">Đăng ký tại đây</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // HÀM TOGGLE PASSWORD
        function setupPasswordToggle(toggleBtnId, inputId, iconId) {
            const toggleButton = document.getElementById(toggleBtnId);
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            if (toggleButton) { 
                toggleButton.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    eyeIcon.className = type === 'text' ? 'fa fa-eye-slash' : 'fa fa-eye';
                });
            }
        }
        (function () {
          'use strict'
          const form = document.querySelector('.needs-validation');
          setupPasswordToggle('togglePassword', 'password', 'eyeIcon'); 
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
            // Chuyển hướng
            window.location.href = '<?php echo $redirect_target; ?>';
            <?php endif; ?>
        });
    </script>
    <?php endif; ?>
</body>
</html>