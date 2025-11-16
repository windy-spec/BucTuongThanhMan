<?php
// File: controller/BookingController.php - FINAL SIMULATION VERSION

// Bật hiển thị lỗi
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class BookingController {
    
    private $conn; // Đối tượng PDO

    public function __construct($pdo_connection) {
        $this->conn = $pdo_connection;
        // Giả định session_start() đã có trong config.php
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * ===============================================
     * HÀM HỖ TRỢ: CẬP NHẬT TRẠNG THÁI PHÒNG (Đồng bộ)
     * ===============================================
     */
    private function updateRoomStatusAfterBooking($room_id, $new_status) {
        $sql = "UPDATE rooms SET status = :new_status WHERE id = :room_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['new_status' => $new_status, 'room_id' => $room_id]);
    }

    /**
     * ===============================================
     * HÀM 1: TẠO ĐƠN ĐẶT PHÒNG (CREATE)
     * ===============================================
     */
    public function createBooking($room_id, $check_in, $check_out, $total_price) {
        try {
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("Vui lòng đăng nhập để đặt phòng.");
            }
            if (empty($room_id) || empty($check_in) || empty($check_out) || empty($total_price)) {
                throw new Exception("Vui lòng nhập đầy đủ thông tin đặt phòng.");
            }

            $user_id = $_SESSION['user_id'];
            
            $sql = "INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date, total_price, status) 
                    VALUES (:user_id, :room_id, :check_in, :check_out, :total_price, 'pending')";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'room_id' => $room_id,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'total_price' => $total_price
            ]);
            
            // Cập nhật trạng thái phòng thành 'occupied' ngay sau khi tạo đơn
            if ($stmt->rowCount() > 0) {
                 $this->updateRoomStatusAfterBooking($room_id, 'occupied');
            }

            return "Đặt phòng thành công. Đơn đang chờ xác nhận.";

        } catch (Exception $e) {
            return "Lỗi đặt phòng: " . $e->getMessage();
        }
    }

    /**
     * ===============================================
     * HÀM 2: CẬP NHẬT TRẠNG THÁI ĐƠN (UPDATE) - DÙNG CHO ADMIN
     * ===============================================
     */
    public function updateBookingStatus($booking_id, $new_status) {
        if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'staff')) {
            return "Lỗi: Bạn không có quyền thay đổi trạng thái đơn đặt phòng.";
        }

        try {
            // 1. Lấy room_id trước khi cập nhật
            $sql_room = "SELECT room_id FROM bookings WHERE id = :booking_id";
            $stmt_room = $this->conn->prepare($sql_room);
            $stmt_room->execute(['booking_id' => $booking_id]);
            $room = $stmt_room->fetch(PDO::FETCH_ASSOC);

            // 2. Cập nhật trạng thái đơn hàng
            $sql = "UPDATE bookings SET status = :new_status WHERE id = :booking_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['new_status' => $new_status, 'booking_id' => $booking_id]);

            if ($stmt->rowCount() > 0 && $room) {
                 // 3. ĐỒNG BỘ TRẠNG THÁI PHÒNG
                if ($new_status == 'cancelled' || $new_status == 'checked_out') {
                    // Nếu bị hủy hoặc đã trả phòng, giải phóng phòng
                    $this->updateRoomStatusAfterBooking($room['room_id'], 'available');
                } elseif ($new_status == 'checked_in' || $new_status == 'confirmed') {
                    // Nếu nhận phòng hoặc xác nhận thanh toán, đảm bảo phòng là occupied
                    $this->updateRoomStatusAfterBooking($room['room_id'], 'occupied');
                }
                return "Cập nhật trạng thái đơn #$booking_id thành '$new_status' thành công.";
            } else {
                return "Lỗi: Không tìm thấy đơn hoặc trạng thái không thay đổi.";
            }

        } catch (Exception $e) {
            return "Lỗi hệ thống: " . $e->getMessage();
        }
    }

    /**
     * ===============================================
     * HÀM 3: XÓA ĐƠN ĐẶT PHÒNG (DELETE)
     * ===============================================
     */
    public function deleteBooking($booking_id) {
        $role = $_SESSION['role'] ?? 'guest';
        $user_id = $_SESSION['user_id'] ?? 0;
        
        // 1. Lấy room_id trước khi xóa (Cần cho cả Admin và Customer)
        $sql_room = "SELECT room_id FROM bookings WHERE id = :booking_id";
        $stmt_room = $this->conn->prepare($sql_room);
        $stmt_room->execute(['booking_id' => $booking_id]);
        $room = $stmt_room->fetch(PDO::FETCH_ASSOC);

        if (!$room) {
            return "Lỗi: Không tìm thấy đơn đặt phòng để xóa.";
        }
        $room_id = $room['room_id'];

        // 2. Thực hiện xóa (Logic RBAC giữ nguyên)
        if ($role == 'admin' || $role == 'staff') {
            $sql = "DELETE FROM bookings WHERE id = :booking_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['booking_id' => $booking_id]);
        } elseif ($role == 'customer') {
            $sql = "DELETE FROM bookings WHERE id = :booking_id AND user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute(['booking_id' => $booking_id, 'user_id' => $user_id]);
        } else {
             return "Lỗi: Bạn không có quyền xóa đơn này.";
        }

        if ($stmt->rowCount() > 0) {
             // 3. GIẢI PHÓNG PHÒNG
            $this->updateRoomStatusAfterBooking($room_id, 'available');
            return "Xóa đơn thành công. Phòng $room_id đã được giải phóng.";
        } else {
            return "Lỗi: Bạn không có quyền xóa đơn này hoặc đơn không tồn tại.";
        }
    }
    
    /**
     * ===============================================
     * HÀM 7: XỬ LÝ THANH TOÁN GIẢ LẬP (SIMULATION)
     * ===============================================
     */
    public function updatePaymentStatusSimulate($booking_id, $amount_paid) {
        if (!isset($_SESSION['user_id'])) {
            return "Lỗi: Phiên làm việc không tồn tại.";
        }
        
        try {
            // Cập nhật status thành 'confirmed'
            $sql = "UPDATE bookings 
                    SET status = 'confirmed' 
                    WHERE id = :booking_id AND user_id = :user_id 
                    AND status = 'pending'"; // Chỉ cập nhật đơn đang chờ (pending)
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'booking_id' => $booking_id,
                'user_id' => $_SESSION['user_id'],
            ]);

            if ($stmt->rowCount() > 0) {
                // Phòng đã được đặt ở trạng thái 'occupied' từ lúc createBooking, không cần cập nhật lại
                return "Thanh toán (Giả lập) thành công! Đơn hàng đã được xác nhận.";
            } else {
                return "Lỗi: Đơn hàng không tìm thấy, đã thanh toán, hoặc không thuộc về bạn.";
            }

        } catch (Exception $e) {
            return "Lỗi CSDL khi cập nhật trạng thái: " . $e->getMessage();
        }
    }


    // ===============================================
    // CÁC HÀM READ (READ ALL, READ RECENT, READ USER, MAP)
    // ===============================================

    public function getAllBookings() {
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
            return [];
        }

        $sql = "SELECT 
                    b.id, u.username AS guest_name, r.room_number, b.check_in_date, 
                    b.check_out_date, b.total_price, b.status, b.user_id
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN rooms r ON b.room_id = r.id
                ORDER BY b.check_in_date DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserBookings($user_id) {
        if (!isset($_SESSION['role']) || $_SESSION['role'] != 'customer' || $user_id != ($_SESSION['user_id'] ?? 0)) {
            return [];
        }

        $sql = "SELECT 
                    b.id, r.room_number, b.check_in_date, b.check_out_date, b.total_price, b.status
                FROM bookings b
                JOIN rooms r ON b.room_id = r.id
                WHERE b.user_id = :user_id
                ORDER BY b.check_in_date DESC";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['user_id' => $user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRecentBookings() {
        $sql = "SELECT 
                    b.id, u.username AS guest_name, r.room_number, b.check_in_date, 
                    b.check_out_date, b.status
                FROM bookings b
                JOIN users u ON b.user_id = u.id
                JOIN rooms r ON b.room_id = r.id
                ORDER BY b.check_in_date DESC
                LIMIT 5";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getRoomStatusMap() {
        $sql = "SELECT id,room_number, status FROM rooms ORDER BY room_number ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>