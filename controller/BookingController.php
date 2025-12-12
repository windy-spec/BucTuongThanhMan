<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class BookingController {
    
    private $conn; 

    public function __construct($pdo_connection) {
        $this->conn = $pdo_connection;
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->autoCancelOverdueBookings();
    }
    private function autoCancelOverdueBookings() {
        try {
            // 1. Tìm các đơn thỏa mãn điều kiện hủy
            // DATEDIFF(check_out, check_in) > 15: Thuê trên 15 ngày
            // created_at < NOW() - INTERVAL 24 HOUR: Đã tạo quá 24h
            $sql = "SELECT id, room_id FROM bookings 
                    WHERE status = 'pending' 
                    AND DATEDIFF(check_out_date, check_in_date) > 15 
                    AND created_at < (NOW() - INTERVAL 24 HOUR)";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $overdue_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($overdue_bookings) {
                // 2. Duyệt qua từng đơn để hủy và trả phòng
                foreach ($overdue_bookings as $booking) {
                    // Cập nhật trạng thái đơn thành 'cancelled'
                    $cancelSql = "UPDATE bookings SET status = 'cancelled' WHERE id = :id";
                    $cancelStmt = $this->conn->prepare($cancelSql);
                    $cancelStmt->execute(['id' => $booking['id']]);
                    // Quan trọng: Cập nhật trạng thái phòng thành 'available'
                    $this->updateRoomStatusAfterBooking($booking['room_id'], 'available');
                }
            }
        } catch (Exception $e) {
            error_log("Auto Cancel Error: " . $e->getMessage());
        }
    }

    /**
     * ===============================================
     * HÀM HỖ TRỢ: CẬP NHẬT TRẠNG THÁI PHÒNG
     * ===============================================
     */
    private function updateRoomStatusAfterBooking($room_id, $new_status) {
        $sql = "UPDATE rooms SET status = :new_status WHERE id = :room_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['new_status' => $new_status, 'room_id' => $room_id]);
    }

   /**
     * ===============================================
     * HÀM 1: TẠO ĐƠN ĐẶT PHÒNG 
     * ===============================================
     */
    public function createBooking($room_id, $check_in, $check_out) { 
        try {
            // 1. Kiểm tra đăng nhập
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("Vui lòng đăng nhập để đặt phòng.");
            }
            if (empty($room_id) || empty($check_in) || empty($check_out)) {
                throw new Exception("Vui lòng nhập đầy đủ thông tin.");
            }
            // 2. Lấy giá gốc của phòng từ Database
            // Sửa 'price' thành 'base_price' (hoặc tên đúng trong bảng rooms của bạn)
           $sql = "SELECT rt.base_price 
                    FROM rooms r 
                    JOIN room_types rt ON r.room_type_id = rt.id 
                    WHERE r.id = :room_id";

            $stmtPrice = $this->conn->prepare($sql);
            $stmtPrice->execute(['room_id' => $room_id]);
            $roomData = $stmtPrice->fetch(PDO::FETCH_ASSOC);

            if (!$roomData) {
                throw new Exception("Phòng không tồn tại hoặc loại phòng chưa có giá.");
            }

        
            $base_price = $roomData['base_price'];

            // 3. Tính toán ngày và tổng tiền (Logic tăng 10% T7, CN)
            $start_date = new DateTime($check_in);
            $end_date = new DateTime($check_out);

            if ($start_date >= $end_date) {
                throw new Exception("Ngày trả phòng phải sau ngày nhận phòng.");
            }

            $total_calculated = 0;
            $curr_date = clone $start_date;
            // Vòng lặp qua từng ngày để cộng tiền
            while ($curr_date < $end_date) {
                $day_of_week = $curr_date->format('N'); // 1 (Thứ 2) -> 7 (CN)
                // Nếu là Thứ 7 (6) hoặc CN (7) thì tăng 10%
                if ($day_of_week == 6 || $day_of_week == 7) {
                    $total_calculated += $base_price * 1.1; 
                } else {
                    $total_calculated += $base_price;
                }
                
                $curr_date->modify('+1 day');
            }

            // Kiểm tra giới hạn 30 ngày
            $interval = $start_date->diff($end_date);
            $days = $interval->days;

            if ($days >= 30) {
                throw new Exception("Xin lỗi, chúng tôi chỉ nhận đặt phòng dưới 30 ngày.");
            }

            $user_id = $_SESSION['user_id'];
            
            // 4. Insert vào DB với giá ĐÃ TÍNH TOÁN ($total_calculated)
            $sql = "INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date, total_price, status, created_at) 
                    VALUES (:user_id, :room_id, :check_in, :check_out, :total_price, 'pending', NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'room_id' => $room_id,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'total_price' => $total_calculated // Dùng biến tự tính
            ]);
            
            if ($stmt->rowCount() > 0) {
                 $this->updateRoomStatusAfterBooking($room_id, 'occupied');
            }

            // Trả về kết quả
            if ($days > 15) {
                return [
                    'status' => 'warning',
                    'message' => "Đặt phòng thành công! Tổng tiền: " . number_format($total_calculated) . " VND. <br>Lưu ý: Đặt dài hạn vui lòng thanh toán trong 24h."
                ];
            } else {
                return [
                    'status' => 'success',
                    'message' => "Đặt phòng thành công. Tổng tiền: " . number_format($total_calculated) . " VND."
                ];
            }

        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => "Lỗi đặt phòng: " . $e->getMessage()
            ];
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
        
        // 1. Lấy room_id trước khi xóa (QUAN TRỌNG)
        // Chúng ta lấy cả ID phòng để trả trạng thái
        $sql_room = "SELECT room_id FROM bookings WHERE id = :booking_id";
        $stmt_room = $this->conn->prepare($sql_room);
        $stmt_room->execute(['booking_id' => $booking_id]);
        $booking = $stmt_room->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            return "Lỗi: Không tìm thấy đơn đặt phòng.";
        }
        $room_id = $booking['room_id'] ?? null; 
        
        if (!$room_id) {
            error_log("Lỗi: Không lấy được room_id từ đơn hàng $booking_id");
        }
        // 2. Thực hiện xóa (Logic phân quyền)
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

        // 3. KIỂM TRA VÀ CẬP NHẬT PHÒNG
        if ($stmt->rowCount() > 0) {
            // Nếu xóa thành công và có room_id -> Trả phòng về 'available'
            if ($room_id) {
                $this->updateRoomStatusAfterBooking($room_id, 'available');
            }
            return "Hủy đơn thành công!";
        } else {
            return "Lỗi: Không thể xóa đơn (Có thể đơn không còn tồn tại hoặc không thuộc về bạn).";
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
    
    public function validateBookingDuration($checkInDate, $checkOutDate) {
        $start = new DateTime($checkInDate);
        $end = new DateTime($checkOutDate);
        
        // Tính khoảng cách ngày
        $interval = $start->diff($end);
        $days = $interval->days;

        // QUY TẮC 1: Chặn nếu đặt >= 30 ngày
        if ($days >= 30) {
            return [
                'valid' => false, 
                'message' => 'Xin lỗi, chúng tôi chỉ nhận đặt phòng dưới 30 ngày.'
            ];
        }

        // QUY TẮC 2: Cảnh báo nếu > 15 ngày
        if ($days > 15) {
            return [
                'valid' => true,
                'requires_urgent_payment' => true, // Cờ đánh dấu để hiện thông báo
                'days' => $days
            ];
        }

        return ['valid' => true, 'requires_urgent_payment' => false];
    }
}
?>