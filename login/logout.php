<?php
// Không cần session_start() nếu đã có trong config.php

// 1. Xóa tất cả các biến session
$_SESSION = array();

// 2. Hủy cookie session (Đảm bảo session bị xóa hoàn toàn)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 3. Hủy session
session_destroy();

// 4. Chuyển hướng người dùng về trang đăng nhập
header("Location: ../index.php"); 
exit;
?>