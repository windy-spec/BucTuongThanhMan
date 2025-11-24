<?php
// 1. CẤU HÌNH CHUNG CHO PHP
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Bật hiển thị lỗi (Để dễ gỡ lỗi, khi nào hoàn thiện web 100% thì nên tắt đi)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// 2. TỰ ĐỘNG NHẬN DIỆN MÔI TRƯỜNG (LOCALHOST HAY HOSTING)
// Kiểm tra tên miền hiện tại để chọn cấu hình phù hợp
if ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['HTTP_HOST'] == '127.0.0.1') {
    
    // --- TRƯỜNG HỢP 1: CHẠY Ở MÁY NHÀ (LOCALHOST) ---
    $db_host = 'localhost';
    $db_name = 'QLKS';          // Tên Database trong XAMPP/WAMP
    $db_user = 'root';          // User mặc định của XAMPP
    $db_pass = '';              // Pass mặc định của XAMPP (thường rỗng)
    
    // Đường dẫn gốc ở máy nhà (Đã sửa lỗi dư dấu : của bạn)
    define('BASE_URL', 'http://localhost/QLKS/'); 

} else {

    // --- TRƯỜNG HỢP 2: CHẠY TRÊN INFINITYFREE ---
    $db_host = 'sql100.infinityfree.com'; // Lấy từ ảnh bạn gửi
    $db_name = 'if0_40426028_QLKS';       // Lấy từ ảnh bạn gửi
    $db_user = 'if0_40426028';            // Lấy từ ảnh bạn gửi
    
    // [QUAN TRỌNG] Dán mật khẩu vPanel/FTP của bạn vào giữa hai dấu nháy đơn bên dưới:
    $db_pass = 'DÁN_MẬT_KHẨU_CỦA_BẠN_VÀO_ĐÂY'; 

    // Đường dẫn gốc trên hosting (Bạn kiểm tra lại tên miền chính xác nhé)
    // Ví dụ: http://buctuongthanhman.infinityfreeapp.com/
    define('BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/');
}

// 3. KẾT NỐI CSDL (PDO)
$charset = 'utf8mb4';
$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    // Tạo kết nối PDO
    $conn = new PDO($dsn, $db_user, $db_pass, $options);
} catch (\PDOException $e) {
    // Nếu lỗi kết nối, dừng chương trình và báo lỗi
    // (Trên web thực tế nên ẩn $e->getMessage() đi để bảo mật, nhưng giờ đang học thì cứ để)
    die("Lỗi kết nối Database: " . $e->getMessage());
}

// 4. KHỞI ĐỘNG SESSION (Nếu chưa có)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>