<?php
// File này phải được include_once ở đầu mọi trang user

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 1. Kiểm tra xem đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/index.php");
    exit();
}

// 2. Kiểm tra xem có phải là Khách hàng không (Hoặc Admin/Staff nếu bạn muốn họ đặt phòng)
// Chúng ta sẽ giới hạn chỉ cho vai trò 'customer' quản lý đơn của họ
if ($_SESSION['role'] != 'customer') {
    http_response_code(403);
    die("<h1>Lỗi 403: Truy cập bị cấm</h1><p>Trang này chỉ dành cho khách hàng.</p>");
}

// Gán biến customer_id và username để sử dụng trong các trang
$customer_id = $_SESSION['user_id'];
$customer_username = $_SESSION['username'];
?>