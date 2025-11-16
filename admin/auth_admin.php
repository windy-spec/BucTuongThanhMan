<?php
// File: admin/auth_admin.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Kiểm tra xem đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    // Nếu chưa, đá về trang đăng nhập
    header("Location: ../login/index.php");
    exit();
}

// 2. Kiểm tra xem có phải là Admin không
if ($_SESSION['role'] != 'admin') {
    // Nếu không phải admin, báo lỗi và dừng lại
    http_response_code(403); // Lỗi 403 Forbidden
    die("<h1>Lỗi 403: Truy cập bị cấm</h1><p>Bạn không có quyền truy cập trang này.</p>");
}

// Nếu vượt qua 2 cổng, người dùng là Admin
$admin_id = $_SESSION['user_id'];
$admin_username = $_SESSION['username'];
?>