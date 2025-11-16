<?php
// 1. SỬA LẠI: Lùi 1 cấp ('../') ra thư mục gốc để tìm 'config.php'
include_once(__DIR__ . '/../config.php');

// 2. SỬA LẠI: Lùi 1 cấp ('../'), vào 'controller' và tìm 'authController.php'
include_once(__DIR__ . '/../controller/authController.php');

// 3. Khởi tạo Controller
// Lưu ý: Tên class 'AuthController' phải đúng với tên class
// bên trong tệp authController.php của bạn
$authController = new AuthController($conn);

// 4. Chạy hàm xử lý đăng ký
$authController->handleRegister();
?>