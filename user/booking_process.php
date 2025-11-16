<?php
// FILE: user/booking_process.php

// 1. GỌI CỔNG BẢO VỆ (để lấy $customer_id, $conn, và $_SESSION['role'])
include_once('auth_customer.php'); 
include_once(__DIR__ . '/../config.php'); 
include_once(__DIR__ . '/../controller/BookingController.php'); 

// 2. KHỞI TẠO VÀ LẤY DỮ LIỆU
$bookingController = new BookingController($conn);
$action = isset($_POST['action']) ? $_POST['action'] : '';
$result_message = "Hành động không xác định.";

try {
    if ($action == 'create_booking') {
        // Logic 1: TẠO ĐƠN
        $room_id = $_POST['room_id'];
        $check_in = $_POST['check_in_date'];
        $check_out = $_POST['check_out_date'];
        $total_price = $_POST['total_price']; 
        
        $result_message = $bookingController->createBooking($room_id, $check_in, $check_out, $total_price);

    } elseif ($action == 'process_payment_simulate') { 
        // Logic 2: THANH TOÁN GIẢ LẬP <-- ĐÃ THÊM KHỐI THIẾU NÀY
        $booking_id = $_POST['booking_id'];
        $amount = $_POST['amount'];

        // Gọi hàm giả lập để chuyển trạng thái đơn hàng
        $result_message = $bookingController->updatePaymentStatusSimulate($booking_id, $amount);
        
    } elseif ($action == 'delete_booking') {
        // Logic 3: XÓA/HỦY ĐƠN
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
header("Location: my_bookings.php");
exit();
?>