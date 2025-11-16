<?php
session_start();
include_once(__DIR__ . '/../config.php');

$token_error = '';
$token_is_valid = false;
$token = $_GET['token'] ?? '';

// Nếu không có token, báo lỗi ngay
if (empty($token)) {
    $token_error = "Không tìm thấy mã khôi phục (Token).";
} else {
    // 1. TẠO HASH TỪ TOKEN GỐC
    $token_hash = hash('sha256', $token);

    // 2. TÌM NGƯỜI DÙNG BẰNG TOKEN HASH và KIỂM TRA HẾT HẠN TRỰC TIẾP TRONG SQL
    // CHỈ DỰA VÀO KIỂM TRA SQL (reset_token_expires_at > NOW())
    $sql = "SELECT id FROM users 
            WHERE reset_token_hash = :token_hash 
            AND reset_token_expires_at > NOW()";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute(['token_hash' => $token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Nếu không tìm thấy người dùng (hoặc đã hết hạn do điều kiện NOW() trong SQL)
        $token_error = "Mã khôi phục không hợp lệ hoặc đã hết hạn.";
    } else {
        // Token hợp lệ (SQL đã xác nhận cả hash và thời gian)
        $token_is_valid = true;
    }
}

// Lấy thông báo lỗi/thành công từ process nếu có
$message = $_SESSION['message'] ?? '';
$type = (strpos($message, 'Lỗi') !== false) ? 'error' : 'success';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt lại Mật khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .reset-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reset-card { width: 100%; max-width: 450px; border-radius: 0.75rem; }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="card shadow-lg reset-card">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="fa fa-key fa-3x text-info"></i>
                    <h4 class="mt-3 fw-bold">Đặt lại Mật khẩu mới</h4>
                </div>
                
                <?php if ($token_error): ?>
                    <div class="alert alert-danger text-center" role="alert">
                        <?php echo $token_error; ?>
                        <div class="mt-2"><a href="forgot.php">Thử lại</a></div>
                    </div>
                <?php elseif ($token_is_valid): ?>
                    <form action="reset_process.php" method="POST" class="needs-validation" novalidate>
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Mật khẩu mới:</label>
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
                            <label for="confirm_password" class="form-label fw-semibold">Nhập lại Mật khẩu mới:</label>
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
                            <button type="submit" class="btn btn-info btn-lg fw-bold text-white">
                                <i class="fa fa-sync-alt"></i> Đặt lại Mật khẩu
                            </button>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-4">
                    <a href="index.php" class="small text-decoration-none">Quay lại Đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function setupPasswordToggle(toggleBtnId, inputId, iconId) {
            const toggleButton = document.getElementById(toggleBtnId);
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);

            if (toggleButton) {
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
            if (!form) return; // Nếu form không hiển thị (do lỗi token), dừng script

            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');

            function validatePasswordMatch() {
                if (passwordInput.value !== confirmPasswordInput.value) {
                    confirmPasswordInput.setCustomValidity('Password Mismatch');
                } else {
                    confirmPasswordInput.setCustomValidity('');
                }
            }

            setupPasswordToggle('togglePassword1', 'password', 'eyeIcon1'); 
            setupPasswordToggle('togglePassword2', 'confirm_password', 'eyeIcon2');

            passwordInput.addEventListener('change', validatePasswordMatch);
            confirmPasswordInput.addEventListener('change', validatePasswordMatch);

            form.addEventListener('submit', function (event) {
                validatePasswordMatch();
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
            icon: '<?php echo $type; ?>',
            title: 'Thông báo',
            text: '<?php echo str_replace("'", "\'", $message); ?>',
            confirmButtonText: 'OK'
        });
    </script>
    <?php endif; ?>
</body>
</html>