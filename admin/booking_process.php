<?php
// BƯỚC 1: BẮT BUỘC KHỞI ĐỘNG SESSION TRƯỚC KHI TRUY CẬP BẤT KỲ DỮ LIỆU NÀO
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. KIỂM TRA QUYỀN TRƯỚC KHI GỌI CONFIG (code của bạn)
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'staff')) {
    $_SESSION['message'] = "Phiên làm việc đã hết hạn hoặc bạn không có quyền thực hiện hành động này.";
    header("Location: ../login/index.php");
    exit();
}

// 3. GỌI CONFIG VÀ CONTROLLER (Sau khi Session và Quyền đã được xác nhận)
include_once(__DIR__ . '/../config.php'); 
include_once(__DIR__ . '/../controller/BookingController.php');

// 3. KHỞI TẠO VÀ LẤY DỮ LIỆU
$bookingController = new BookingController($conn);
$action = isset($_POST['action']) ? $_POST['action'] : '';
$result_message = "Hành động không xác định.";

try {
    if ($action == 'update_status') {
        $booking_id = $_POST['booking_id'];
        $new_status = $_POST['new_status'];
        
        // Gọi hàm cập nhật trạng thái
        $result_message = $bookingController->updateBookingStatus($booking_id, $new_status);

    } elseif ($action == 'delete_booking') {
        // Hành động này được hỗ trợ cho Admin/Staff trong BookingController
        $booking_id = $_POST['booking_id'];
        $result_message = $bookingController->deleteBooking($booking_id);

    } else {
        $result_message = "Lỗi: Hành động không được hỗ trợ.";
    }

} catch (Exception $e) {
    $result_message = "Lỗi hệ thống: " . $e->getMessage();
}

// 4. LƯU KẾT QUẢ VÀ CHUYỂN HƯỚNG TRỞ LẠI
$_SESSION['message'] = $result_message;
header("Location: manage_bookings.php");
exit();
?>