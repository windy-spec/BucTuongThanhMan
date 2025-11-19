<?php
// File: controller/BookingController.php

// Bật hiển thị lỗi (Chỉ dùng khi dev, nên tắt khi production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class BookingController {
    
    private $conn; // Đối tượng PDO

    public function __construct($pdo_connection) {
        $this->conn = $pdo_connection;
        
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // --- [MỚI] TỰ ĐỘNG QUÉT VÀ HỦY ĐƠN QUÁ HẠN KHI KHỞI TẠO ---
        // Mỗi lần Controller được gọi, nó sẽ kiểm tra xem có đơn nào cần hủy không
        $this->autoCancelOverdueBookings();
    }

    /**
     * ===============================================
     * [LOGIC MỚI] TỰ ĐỘNG HỦY ĐƠN (Auto Cancel)
     * ===============================================
     * Điều kiện hủy:
     * 1. Trạng thái 'pending' (chưa thanh toán)
     * 2. Thời gian lưu trú > 15 ngày
     * 3. Đã tạo quá 24 giờ trước
     */
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
            // Ghi log lỗi vào file hệ thống thay vì hiện ra màn hình để tránh làm phiền người dùng
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
     * HÀM 1: TẠO ĐƠN ĐẶT PHÒNG (CREATE)
     * ===============================================
     */
    public function createBooking($room_id, $check_in, $check_out, $total_price) {
        try {
            // 1. Kiểm tra đăng nhập và dữ liệu rỗng
            if (!isset($_SESSION['user_id'])) {
                throw new Exception("Vui lòng đăng nhập để đặt phòng.");
            }
            if (empty($room_id) || empty($check_in) || empty($check_out) || empty($total_price)) {
                throw new Exception("Vui lòng nhập đầy đủ thông tin đặt phòng.");
            }

            // 2. LOGIC MỚI: Tính toán số ngày
            $start_date = new DateTime($check_in);
            $end_date = new DateTime($check_out);

            if ($start_date >= $end_date) {
                throw new Exception("Ngày trả phòng phải sau ngày nhận phòng.");
            }

            $interval = $start_date->diff($end_date);
            $days = $interval->days; // Số ngày lưu trú

            // 3. LOGIC MỚI: Chặn nếu >= 30 ngày
            if ($days >= 30) {
                throw new Exception("Xin lỗi, chúng tôi chỉ nhận đặt phòng dưới 30 ngày.");
            }

            $user_id = $_SESSION['user_id'];
            
            // 4. Thêm đơn vào CSDL
            $sql = "INSERT INTO bookings (user_id, room_id, check_in_date, check_out_date, total_price, status, created_at) 
                    VALUES (:user_id, :room_id, :check_in, :check_out, :total_price, 'pending', NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                'user_id' => $user_id,
                'room_id' => $room_id,
                'check_in' => $check_in,
                'check_out' => $check_out,
                'total_price' => $total_price
            ]);
            
            // 5. Cập nhật trạng thái phòng
            if ($stmt->rowCount() > 0) {
                 $this->updateRoomStatusAfterBooking($room_id, 'occupied');
            }

            // 6. LOGIC MỚI: Trả về kết quả dựa trên số ngày (> 15 ngày thì cảnh báo)
            if ($days > 15) {
                return [
                    'status' => 'warning', // Dùng để hiện icon màu vàng
                    'message' => "Đặt phòng thành công! <br><b>Lưu ý:</b> Vì bạn đặt dài hạn ($days ngày), vui lòng thanh toán trong vòng <b>24 giờ</b>, nếu không đơn sẽ bị hủy tự động."
                ];
            } else {
                return [
                    'status' => 'success', // Dùng để hiện icon màu xanh
                    'message' => "Đặt phòng thành công. Đơn đang chờ xác nhận."
                ];
            }

        } catch (Exception $e) {
            // Trả về mảng lỗi để đồng bộ
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

        // --- KIỂM TRA KỸ ROOM ID ---
        // Đảm bảo lấy đúng tên cột (phòng trường hợp bạn đặt tên khác trong DB)
        $room_id = $booking['room_id'] ?? null; 
        
        if (!$room_id) {
            // Nếu null, thử tìm các biến thể khác hoặc báo lỗi
            // (Đoạn này giúp debug nếu bạn đặt tên cột là RoomId hay Room_ID)
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