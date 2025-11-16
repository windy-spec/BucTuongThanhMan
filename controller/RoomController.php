<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

class RoomController {

    private $conn; // PDO

    public function __construct($pdo_connection) {
        $this->conn = $pdo_connection;
    }

    /* ---------------------------------------------------------
       HÀM 1: Danh sách phòng + loại phòng
    --------------------------------------------------------- */
    public function getAllRoomsWithTypes() {
        $sql = "SELECT 
                    r.id, 
                    r.room_number, 
                    r.status,
                    rt.type_name, 
                    rt.base_price,
                    r.room_type_id
                FROM rooms r
                JOIN room_types rt ON r.room_type_id = rt.id
                ORDER BY r.room_number ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ---------------------------------------------------------
       HÀM 2: Lấy loại phòng
    --------------------------------------------------------- */
    public function getAllRoomTypes() {
        $sql = "SELECT id, type_name FROM room_types ORDER BY type_name";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ---------------------------------------------------------
       HÀM 3: Thêm phòng
    --------------------------------------------------------- */
    public function createRoom($room_number, $room_type_id) {
        try {
            if (empty($room_number) || empty($room_type_id)) {
                throw new Exception("Vui lòng nhập đầy đủ số phòng và loại phòng.");
            }

            // Kiểm tra phòng trùng
            $check = $this->conn->prepare("SELECT id FROM rooms WHERE room_number = :room_number");
            $check->execute(['room_number' => $room_number]);
            
            if ($check->fetch()) {
                throw new Exception("Số phòng '$room_number' đã tồn tại.");
            }

            $insert = $this->conn->prepare("
                INSERT INTO rooms (room_number, room_type_id, status)
                VALUES (:room_number, :room_type_id, 'available')
            ");

            $insert->execute([
                'room_number' => $room_number,
                'room_type_id' => $room_type_id
            ]);

            return "Thêm phòng thành công!";

        } catch (Exception $e) {
            return "Lỗi: " . $e->getMessage();
        }
    }

    /* ---------------------------------------------------------
       HÀM 4: Sửa phòng
    --------------------------------------------------------- */
    public function updateRoom($room_id, $room_type_id, $status) {
        $sql = "UPDATE rooms 
                SET room_type_id = :room_type_id, status = :status 
                WHERE id = :room_id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            'room_id' => $room_id,
            'room_type_id' => $room_type_id,
            'status' => $status
        ]);

        return "Cập nhật thành công!";
    }

    /* ---------------------------------------------------------
       HÀM 5: Thống kê phòng
    --------------------------------------------------------- */
    public function getRoomStats() {
        $total = $this->conn->query("SELECT COUNT(id) AS total FROM rooms")
                            ->fetch(PDO::FETCH_ASSOC)['total'];

        $status = $this->conn->query("SELECT status, COUNT(id) AS count FROM rooms GROUP BY status")
                             ->fetchAll(PDO::FETCH_KEY_PAIR);

        return [
            'total'     => $total,
            'available' => $status['available'] ?? 0,
            'occupied'  => $status['occupied'] ?? 0,
            'cleaning'  => $status['cleaning'] ?? 0
        ];
    }

    /* ---------------------------------------------------------
       HÀM 6: Xóa phòng
    --------------------------------------------------------- */
    public function deleteRoom($room_id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM rooms WHERE id = :room_id");
            $stmt->execute(['room_id' => $room_id]);
            return "Xóa phòng thành công!";
        } catch (Exception $e) {
            return "Không thể xóa phòng này. Phòng đang có booking!";
        }
    }

    /* ---------------------------------------------------------
       HÀM 7: Lấy phòng trống
    --------------------------------------------------------- */
    public function getAvailableRooms() {
        $sql = "SELECT r.id, r.room_number, rt.type_name, rt.base_price, rt.description
                FROM rooms r
                JOIN room_types rt ON r.room_type_id = rt.id
                WHERE r.status = 'available'
                ORDER BY rt.base_price ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* ---------------------------------------------------------
       HÀM 8: Lấy chi tiết phòng theo ID (CHUẨN)
    --------------------------------------------------------- */
    public function getRoomDetailsById($roomId) {

        $sql = "SELECT 
                    r.*, 
                    rt.type_name AS room_type_name,
                    rt.base_price AS room_type_price,
                    rt.description AS room_type_description
                FROM rooms r
                JOIN room_types rt ON r.room_type_id = rt.id
                WHERE r.id = :id";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $roomId, PDO::PARAM_INT);
        $stmt->execute();
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$room) return null;

        // Nếu phòng đang có khách
        if ($room['status'] === 'occupied') {
            
            $booking_sql = "SELECT 
                                u.username AS guest_name,
                                b.check_out_date AS expected_checkout
                            FROM bookings b
                            JOIN users u ON b.user_id = u.id
                            WHERE b.room_id = :room_id
                            AND b.status = 'checked_in'
                            ORDER BY b.id DESC
                            LIMIT 1";

            $bstmt = $this->conn->prepare($booking_sql);
            $bstmt->bindParam(':room_id', $roomId, PDO::PARAM_INT);
            $bstmt->execute();

            $booking = $bstmt->fetch(PDO::FETCH_ASSOC);

            if ($booking) {
                $room['guest_name'] = $booking['guest_name'];
                $room['expected_checkout'] = $booking['expected_checkout'];
            }
        }

        return $room;
    }
}
?>
