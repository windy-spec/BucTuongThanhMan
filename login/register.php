<?php
    // BẮT BUỘC: KHỞI ĐỘNG SESSION
    session_start();

    // Gọi config
    include_once(__DIR__ . '/../config.php');
    
    // Xử lý LỖI/THÀNH CÔNG từ Server (SweetAlert)
    $message = $_SESSION['message'] ?? '';
    $type = (strpos($message, 'Lỗi') !== false || strpos($message, 'thành công') !== false) ? 'error' : 'success';
    unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng ký - Hệ thống Quản lý</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <style>
        /* ------------------------------------- */
        /* NỀN ẢNH THÀNH PHỐ RÕ NÉT */
        /* ------------------------------------- */
        body { 
            /* Sử dụng đường dẫn tương đối: Đi từ login/ lên 1 cấp (/) -> assets/img/ */
            background-image: url('../assets/img/background.jpg'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            
            /* Lớp phủ tối nhẹ (Dark Overlay) */
            /* Giá trị 0.2 giúp ảnh SÁNG và RÕ NÉT hơn so với 0.3 */
            background-color: rgba(0, 0, 0, 0.3); 
            background-blend-mode: darken;
        }

        /* Đảm bảo nội dung căn giữa và có khoảng đệm */
        .register-container { 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            padding: 30px 20px; /* Thêm padding ngang */
        }

        /* Card nổi bật trên nền */
        .register-card { 
            width: 100%; 
            max-width: 500px; 
            border: none; 
            border-radius: 0.75rem; 
            /* Tăng Box Shadow để form nổi bật hơn trên nền ảnh sáng */
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="card shadow-lg register-card">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fa fa-user-plus fa-3x text-success"></i>
                    <h2 class="mt-3 fw-bold">Tạo tài khoản</h2>
                    <p class="text-muted">Bắt đầu với dự án của chúng tôi.</p>
                </div>
                
                <form action="register_process.php" method="POST" class="needs-validation" novalidate>
                    <div class="mb-3">
                        <label for="username" class="form-label fw-semibold">Tên đăng nhập:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-user"></i></span>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <div class="invalid-feedback">Vui lòng nhập Tên đăng nhập.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">Email:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">Vui lòng nhập Email hợp lệ.</div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label fw-semibold">Số điện thoại:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-phone"></i></span>
                            <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                    </div>
                    <div class="mb-3">
    <label for="password" class="form-label fw-semibold">Mật khẩu:</label>
    <div class="input-group">
        <span class="input-group-text"><i class="fa fa-lock"></i></span>
        <input type="password" class="form-control" id="password" name="password" required minlength="6">
        <div class="invalid-feedback">Mật khẩu phải có ít nhất 6 ký tự.</div>
        <button type="button" class="btn btn-outline-secondary" id="togglePassword1">
            <i class="fa fa-eye" id="eyeIcon1"></i>
        </button>
    </div>
</div>
                    <div class="mb-4">
    <label for="confirm_password" class="form-label fw-semibold">Nhập lại Mật khẩu:</label>
    <div class="input-group">
        <span class="input-group-text"><i class="fa fa-lock"></i></span>
        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
        <div id="password-match-feedback" class="invalid-feedback">Mật khẩu không khớp.</div>
        <button type="button" class="btn btn-outline-secondary" id="togglePassword2">
            <i class="fa fa-eye" id="eyeIcon2"></i>
        </button>
    </div>
</div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-success btn-lg fw-bold">
                            <i class="fa fa-check"></i> Đăng ký
                        </button>
                    </div>
                </form>
                
                <hr class="my-4">
                <div class="text-center">
                    <p class="text-muted mb-0">Đã có tài khoản?</p>
                    <a href="index.php" class="fw-bold text-decoration-none">
                        Đăng nhập tại đây
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        (function () {
          'use strict'
          
          const form = document.querySelector('.needs-validation');
          const passwordInput = document.getElementById('password');
          const confirmPasswordInput = document.getElementById('confirm_password');
          const passwordMatchFeedback = document.getElementById('password-match-feedback');

          // Hàm kiểm tra mật khẩu khớp
          function validatePasswordMatch() {
              if (passwordInput.value !== confirmPasswordInput.value) {
                  confirmPasswordInput.setCustomValidity('Password Mismatch');
              } else {
                  confirmPasswordInput.setCustomValidity(''); // Hợp lệ
              }
          }

          // Gán sự kiện kiểm tra khi giá trị thay đổi
          passwordInput.addEventListener('change', validatePasswordMatch);
          confirmPasswordInput.addEventListener('change', validatePasswordMatch);

          // Kiểm soát việc hiển thị lỗi của Bootstrap
          form.addEventListener('submit', function (event) {
            validatePasswordMatch(); // Chạy kiểm tra mật khẩu lần cuối
            if (!form.checkValidity()) {
              event.preventDefault();
              event.stopPropagation();
            }

            form.classList.add('was-validated');
          }, false)
        })()
    </script>
    
<script>
    // --- HÀM HỖ TRỢ BẬT/TẮT MẬT KHẨU ---
    function setupPasswordToggle(toggleBtnId, inputId, iconId) {
        const toggleButton = document.getElementById(toggleBtnId);
        const passwordInput = document.getElementById(inputId);
        const eyeIcon = document.getElementById(iconId);

        if (toggleButton) {
            toggleButton.addEventListener('click', function () {
                // Chuyển đổi giữa 'password' và 'text'
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Chuyển đổi icon (fa-eye <-> fa-eye-slash)
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

    // --- LOGIC VALIDATION CỦA BOOTSTRAP ---
    (function () {
        'use strict'
        const form = document.querySelector('.needs-validation');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');

        function validatePasswordMatch() {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity('Password Mismatch');
            } else {
                confirmPasswordInput.setCustomValidity('');
            }
        }

        // Kích hoạt hàm Toggle cho cả hai trường mật khẩu
        setupPasswordToggle('togglePassword1', 'password', 'eyeIcon1'); 
        setupPasswordToggle('togglePassword2', 'confirm_password', 'eyeIcon2');

        // Gán sự kiện kiểm tra khi giá trị thay đổi
        passwordInput.addEventListener('change', validatePasswordMatch);
        confirmPasswordInput.addEventListener('change', validatePasswordMatch);

        // Kiểm soát việc hiển thị lỗi của Bootstrap khi submit
        form.addEventListener('submit', function (event) {
            validatePasswordMatch(); // Chạy kiểm tra mật khẩu lần cuối
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false)
    })()
</script>

    <?php if ($message): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Thông báo',
            html: '<?php echo str_replace(["'", "\n"], ["\'", "<br>"], $message); ?>',
            confirmButtonText: 'Đóng'
        });
    </script>
    <?php endif; ?>
</body>
</html>