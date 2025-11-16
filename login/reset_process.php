<?php
session_start();
include_once(__DIR__ . '/../config.php'); 
include_once(__DIR__ . '/../controller/AuthController.php'); 

// Khởi tạo Controller
$authController = new AuthController($conn);
$token = $_POST['token'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Gọi hàm xử lý đặt lại mật khẩu
    $result_message = $authController->resetPassword($token, $password, $confirm_password);
    
    // Lưu kết quả vào Session
    $_SESSION['message'] = $result_message;

    // Nếu thành công, chuyển hướng về trang Đăng nhập
    if (strpos($result_message, 'thành công') !== false) {
        header("Location: index.php");
        exit();
    }
} else {
    // Nếu truy cập trực tiếp, báo lỗi
    $result_message = "Yêu cầu không hợp lệ.";
    $_SESSION['message'] = $result_message;
}

// Nếu lỗi, chuyển hướng về trang reset_password để hiển thị lỗi
header("Location: reset_password.php?token=" . urlencode($token));
exit();
?>