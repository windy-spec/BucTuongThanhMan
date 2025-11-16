<?php
// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class AuthController {
    
    private $conn; // Đây sẽ là đối tượng PDO

    // Hàm khởi tạo, nhận kết nối PDO từ config.php
    public function __construct($pdo_connection) {
        $this->conn = $pdo_connection;
    }

    /**
     * ------------------------------------------------
     * XỬ LÝ ĐĂNG KÝ (REGISTER) - ĐÃ SỬA LƯU SESSION
     * ------------------------------------------------
     */
    public function handleRegister() {
        try {
            if ($_SERVER["REQUEST_METHOD"] != "POST") {
                // Đây là trường hợp hiếm, nhưng cần bảo vệ
                $_SESSION['message'] = "Lỗi: Phương thức truy cập không hợp lệ.";
                header("Location: register.php");
                exit();
            }

            // 1. Lấy dữ liệu và Validate
            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone']);
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            $errors = $this->validateInput($username, $email, $password, $confirm_password);
            if (!empty($errors)) {
                $_SESSION['message'] = implode("<br>", $errors);
                header("Location: register.php");
                exit();
            }

            // 2. Kiểm tra trùng lặp
            $sql_check = "SELECT id FROM users WHERE username = :username OR email = :email";
            $stmt_check = $this->conn->prepare($sql_check);
            $stmt_check->execute(['username' => $username, 'email' => $email]);
            
            if ($stmt_check->fetch()) {
                $_SESSION['message'] = "Tên đăng nhập hoặc Email đã tồn tại.";
                header("Location: register.php");
                exit();
            }

            // 3. Mã hóa mật khẩu và Chèn vào CSDL
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $sql_insert = "INSERT INTO users (username, email, phone, password_hash) 
                           VALUES (:username, :email, :phone, :password_hash)";
            
            $stmt_insert = $this->conn->prepare($sql_insert);
            $stmt_insert->execute([
                'username' => $username,
                'email' => $email,
                'phone' => $phone,
                'password_hash' => $hashed_password
            ]);

            // 4. Thông báo thành công và chuyển hướng về View để hiển thị SWAL
            $_SESSION['message'] = "Đăng ký thành công! Bạn có thể đăng nhập ngay.";
            header("Location: index.php"); 
            exit();

        } catch (Exception $e) {
            $_SESSION['message'] = "Lỗi hệ thống: " . $e->getMessage();
            header("Location: register.php");
            exit();
        }
    }


    /**
     * ------------------------------------------------
     * XỬ LÝ ĐĂNG NHẬP (LOGIN) - ĐÃ SỬA CHO SWEETALERT
     * ------------------------------------------------
     */
    public function handleLogin() {
        
        try {
            if ($_SERVER["REQUEST_METHOD"] != "POST") {
                $_SESSION['message'] = "Lỗi: Phương thức truy cập không hợp lệ.";
                header("Location: index.php");
                exit();
            }

            // 1. Lấy dữ liệu
            $username = trim($_POST['username']);
            $password = $_POST['password'];

            if (empty($username) || empty($password)) {
                $_SESSION['message'] = "Vui lòng nhập Tên đăng nhập và Mật khẩu.";
                header("Location: index.php");
                exit();
            }

            // 2. Tìm người dùng trong CSDL
            $sql_find = "SELECT * FROM users WHERE username = :username";
            $stmt_find = $this->conn->prepare($sql_find);
            $stmt_find->execute(['username' => $username]);
            $user = $stmt_find->fetch(PDO::FETCH_ASSOC);

            // 3. Xác thực mật khẩu
            if ($user && password_verify($password, $user['password_hash'])) {
                
                // 4. Đăng nhập thành công -> Lưu Session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; 

                // 5. TÍNH TOÁN URL ĐÍCH VÀ LƯU VÀO SESSION CHO SWEETALERT
                $target_url = ($user['role'] == 'admin' || $user['role'] == 'staff') 
                              ? '../admin/dashboard.php' 
                              : '../user/my_bookings.php';
                
                $_SESSION['swal_message'] = "Bạn đã đăng nhập thành công. Hệ thống sẽ chuyển hướng trong giây lát.";
                $_SESSION['swal_target'] = $target_url;

                // CHUYỂN HƯỚNG VỀ TRANG LOGIN VIEW để chạy JavaScript
                header("Location: index.php");
                exit();
                
            } else {
                // Sai tên đăng nhập hoặc mật khẩu
                $_SESSION['message'] = "Sai tên đăng nhập hoặc mật khẩu.";
                header("Location: index.php");
                exit();
            }

        } catch (Exception $e) {
            // Lỗi hệ thống/CSDL
            $_SESSION['message'] = "Lỗi hệ thống: " . $e->getMessage();
            header("Location: index.php");
            exit();
        }
    }

    /**
     * ------------------------------------------------
     * HÀM HỖ TRỢ KIỂM TRA DỮ LIỆU
     * ------------------------------------------------
     */
    private function validateInput($username, $email, $password, $confirm_password) {
        $errors = [];
        if (empty($username)) $errors[] = "Tên đăng nhập là bắt buộc.";
        if (empty($email)) $errors[] = "Email là bắt buộc.";
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Định dạng email không hợp lệ.";
        if (empty($password)) $errors[] = "Mật khẩu là bắt buộc.";
        elseif (strlen($password) < 6) $errors[] = "Mật khẩu phải có ít nhất 6 ký tự.";
        if ($password != $confirm_password) $errors[] = "Mật khẩu và xác nhận mật khẩu không khớp!";
        
        return $errors;
    }
    /**
     * ------------------------------------------------
     * HÀM 3: XỬ LÝ QUÊN MẬT KHẨU (FORGOT PASSWORD)
     * ------------------------------------------------
     */
    public function forgotPassword($email) {
        try {
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false; // Email không hợp lệ
            }

            // 1. Tìm người dùng bằng email
            $sql_user = "SELECT * FROM users WHERE email = :email";
            $stmt_user = $this->conn->prepare($sql_user);
            $stmt_user->execute(['email' => $email]);
            $user = $stmt_user->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // RẤT QUAN TRỌNG: KHÔNG BÁO LỖI để tránh rò rỉ thông tin
                return false; 
            }

            // 2. TẠO TOKEN BẢO MẬT
            $token = bin2hex(random_bytes(32)); 
            $token_hash = hash('sha256', $token);
            $expires_at = date("Y-m-d H:i:s", time() + 3600); 

            // 3. LƯU HASH VÀ THỜI GIAN HẾT HẠN VÀO CSDL
            $sql_update = "UPDATE users 
                           SET reset_token_hash = :token_hash, reset_token_expires_at = :expires_at
                           WHERE id = :id";
            $stmt_update = $this->conn->prepare($sql_update);
            $stmt_update->execute([
                'token_hash' => $token_hash,
                'expires_at' => $expires_at,
                'id' => $user['id']
            ]);

            // 4. TRẢ VỀ URL KHÔI PHỤC
            return BASE_URL . "login/reset_password.php?token=" . $token;

        } catch (Exception $e) {
            // Thay vì trả về lỗi SQL, trả về false để đảm bảo bảo mật
            error_log("Forgot Password Error: " . $e->getMessage()); 
            return false;
        }
    }
    /* HÀM 4: XỬ LÝ ĐẶT LẠI MẬT KHẨU (RESET PASSWORD)
     */
    public function resetPassword($token_plain, $password, $confirm_password) {
        try {
            // 1. Validation cơ bản
            if (empty($password) || empty($confirm_password)) {
                return "Lỗi: Vui lòng nhập đầy đủ Mật khẩu và Xác nhận Mật khẩu.";
            }
            if ($password !== $confirm_password) {
                return "Lỗi: Mật khẩu và Xác nhận Mật khẩu không khớp.";
            }
            if (strlen($password) < 6) {
                return "Lỗi: Mật khẩu phải có ít nhất 6 ký tự.";
            }

            // 2. TẠO HASH TỪ TOKEN GỐC
            $token_hash = hash('sha256', $token_plain);

            // 3. TÌM NGƯỜI DÙNG VÀ KIỂM TRA HẾT HẠN
            $sql = "SELECT id FROM users 
                    WHERE reset_token_hash = :token_hash AND reset_token_expires_at > NOW()";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['token_hash' => $token_hash]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                return "Lỗi: Mã khôi phục không hợp lệ hoặc đã hết hạn. Vui lòng thử lại.";
            }
            
            // 4. MÃ HÓA MẬT KHẨU MỚI
            $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // 5. CẬP NHẬT MẬT KHẨU VÀ XÓA TOKEN (RẤT QUAN TRỌNG VỀ BẢO MẬT)
            $sql_update = "UPDATE users 
                           SET password_hash = :new_password, 
                               reset_token_hash = NULL, 
                               reset_token_expires_at = NULL
                           WHERE id = :user_id";
            $stmt_update = $this->conn->prepare($sql_update);
            $stmt_update->execute([
                'new_password' => $new_hashed_password,
                'user_id' => $user['id']
            ]);

            return "Đặt lại mật khẩu thành công! Bạn có thể đăng nhập ngay.";

        } catch (Exception $e) {
            return "Lỗi hệ thống khi đặt lại mật khẩu: " . $e->getMessage();
        }
    }
}
?>