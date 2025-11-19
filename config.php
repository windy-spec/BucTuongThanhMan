        <?php

        // 1. ĐẶT MÚI GIỜ CHUNG CHO PHP (RẤT QUAN TRỌNG)
        date_default_timezone_set('Asia/Ho_Chi_Minh'); 

        // Bật hiển thị lỗi (để gỡ lỗi)
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        define('BASE_URL', 'http://localhost/QLKS/');

        // --- THAY ĐỔI THÔNG TIN KẾT NỐI CỦA BẠN DƯỚI ĐÂY ---
        $db_host = 'localhost';     // Thường là 'localhost'
        $db_name = 'QLKS';  // Tên Database bạn tạo trong phpMyAdmin
        $db_user = 'root';          // Tên người dùng CSDL (thường là 'root' trên localhost)
        $db_pass = '';              // Mật khẩu CSDL (thường là rỗng trên localhost)
        // --------------------------------------------------
        $charset = 'utf8mb4';

        // DSN (Data Source Name)
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";

        // Các tùy chọn cho PDO
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            // Tạo đối tượng PDO và gán vào biến $conn
            $conn = new PDO($dsn, $db_user, $db_pass, $options);
            
        } catch (\PDOException $e) {
            // Nếu kết nối thất bại, hiển thị lỗi và dừng chương trình
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
        if (session_status() == PHP_SESSION_NONE) {
        session_start();
        }
    
        ?>