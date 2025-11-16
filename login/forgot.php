<?php
    session_start();
    include_once(__DIR__ . '/../config.php');
    
    $message = $_SESSION['message'] ?? '';
    // Lấy loại thông báo và link debug
    $message_type = $_SESSION['message_type'] ?? 'success'; 
    $debug_link = $_SESSION['DEBUG_RESET_LINK'] ?? '';
    
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
    unset($_SESSION['DEBUG_RESET_LINK']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quên Mật Khẩu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .reset-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .reset-card { width: 100%; max-width: 400px; border-radius: 0.75rem; }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="card shadow-lg reset-card">
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <i class="fa fa-lock-open fa-3x text-warning"></i>
                    <h4 class="mt-3 fw-bold">Khôi phục Mật khẩu</h4>
                    <p class="text-muted small">Nhập email của bạn để nhận liên kết đặt lại.</p>
                </div>
                
                <form action="forgot_process.php" method="POST">
                    <div class="mb-4">
                        <label for="email" class="form-label fw-semibold">Email:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fa fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-warning btn-lg fw-bold text-white">
                            <i class="fa fa-paper-plane"></i> Gửi Yêu Cầu
                        </button>
                    </div>
                </form>
                <div class="text-center mt-3">
                    <a href="index.php" class="small text-decoration-none">Quay lại Đăng nhập</a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <?php if ($message): ?>
    <script>
        // Lấy dữ liệu từ PHP
        const isSuccess = '<?php echo $message_type; ?>' === 'success';
        const debugLink = '<?php echo addslashes($debug_link); ?>';
        
        let htmlContent = '<?php echo str_replace(["'", "\n"], ["\'", "<br>"], $message); ?>';
        
        // Thêm link debug vào nội dung nếu có và là thành công (Chỉ dành cho bạn)
        if (isSuccess && debugLink) {
            htmlContent += '<hr><small class="text-muted fw-bold">DEBUG LINK:</small><br>';
            htmlContent += '<a href="' + debugLink + '">' + debugLink + '</a>';
        }

        Swal.fire({
            icon: isSuccess ? 'success' : 'error',
            title: isSuccess ? 'Thành công!' : 'Thông báo',
            html: htmlContent,
            confirmButtonText: 'Đóng',
            allowOutsideClick: false // Ngăn chặn người dùng đóng mà không bấm nút
        }).then((result) => {
             // Chuyển hướng về trang Đăng nhập sau khi thông báo đóng
            if (isSuccess) {
                window.location.href = 'index.php'; 
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>