<?php

// 1. Kiểm tra phương thức POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
 $_SESSION['message'] = "Lỗi: Phương thức truy cập không hợp lệ.";
 header("Location: index.php");
 exit();
}
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// 2. Gọi Controller
include_once(__DIR__ . '/../config.php'); 
include_once(__DIR__ . '/../controller/AuthController.php'); 

// 3. Khởi tạo và xử lý
// $conn phải được khởi tạo trong config.php
$authController = new AuthController($conn); 
$authController->handleLogin(); 

// Chú ý: Toàn bộ logic kết thúc trong handleLogin(), nên không cần code sau dòng này.
?>