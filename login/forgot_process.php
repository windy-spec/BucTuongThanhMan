<?php
// FILE: login/forgot_process.php - ĐÃ CẬP NHẬT ĐỂ SIMULATE GỬI EMAIL VÀ CHUYỂN HƯỚNG

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include_once(__DIR__ . '/../config.php'); 
include_once(__DIR__ . '/../controller/AuthController.php'); 

$authController = new AuthController($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email'] ?? '');
    
    $result = $authController->forgotPassword($email); // Trả về URL nếu thành công, false nếu lỗi
    
    // 1. Nếu $result là URL (thành công)
    if ($result) {
        // Ghi URL vào session để CÁ NHÂN BẠN (DEVELOPER) có thể kiểm tra.
        $_SESSION['DEBUG_RESET_LINK'] = $result; 
        
        // Tin nhắn cho người dùng cuối:
        $_SESSION['message'] = "Thành công! Nếu địa chỉ email này tồn tại trong hệ thống, liên kết đặt lại mật khẩu đã được gửi đến hộp thư của bạn. Vui lòng kiểm tra email.";
        $_SESSION['message_type'] = 'success'; // Đánh dấu là thành công

    } else {
        // Email không tồn tại hoặc lỗi khác
        $_SESSION['message'] = "Nếu địa chỉ email của bạn tồn tại trong hệ thống, liên kết khôi phục đã được gửi.";
        $_SESSION['message_type'] = 'error'; 
    }
    
} else {
    $_SESSION['message'] = "Yêu cầu không hợp lệ.";
    $_SESSION['message_type'] = 'error';
}

// Chuyển hướng về forgot.php để hiển thị thông báo
header("Location: forgot.php");
exit(); 
?>