<?php

// 2. GỌI CONFIG VÀ CONTROLLER
include_once(__DIR__ . '/../config.php'); // Lấy $conn
include_once(__DIR__ . '/../controller/RoomController.php'); // Lấy class

// 3. KIỂM TRA QUYỀN ADMIN (Rất quan trọng!)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    // Lưu thông báo lỗi
    $_SESSION['message'] = "Lỗi: Bạn không có quyền thực hiện hành động này.";
    // Chuyển hướng về trang chủ hoặc trang đăng nhập
    header("Location: ../login/index.php");
    exit();
}

// 4. KHỞI TẠO CONTROLLER
$roomController = new RoomController($conn);

// 5. XỬ LÝ HÀNH ĐỘNG (action)
$action = isset($_POST['action']) ? $_POST['action'] : '';

try {
    if ($action == 'create_room') {
        // Lấy dữ liệu từ form Thêm phòng
        $room_number = trim($_POST['room_number']);
        $room_type_id = $_POST['room_type_id'];

        // Gọi hàm createRoom và lưu kết quả
        $result_message = $roomController->createRoom($room_number, $room_type_id);
        
        // Lưu kết quả vào session để hiển thị
        $_SESSION['message'] = $result_message;

    } elseif ($action == 'delete_room') {
        // Lấy ID phòng từ form Xóa
        $room_id = $_POST['room_id'];
        
        // Gọi hàm deleteRoom
        $result_message = $roomController->deleteRoom($room_id);
        
        // Lưu kết quả
        $_SESSION['message'] = $result_message;

    } 
    
    elseif ($action == 'update_room') {
        // Lấy dữ liệu từ Modal Sửa
        $room_id = $_POST['room_id'];
        $room_type_id = $_POST['room_type_id_edit']; // Tên field trong modal
        $status = $_POST['status_edit']; // Tên field trong modal
        
        // Gọi hàm updateRoom và lưu kết quả
        $result_message = $roomController->updateRoom($room_id, $room_type_id, $status);
        
        $_SESSION['message'] = $result_message;

    }

    else {
        // Nếu không có action nào hợp lệ
        $_SESSION['message'] = "Lỗi: Hành động không xác định.";
    }

} catch (Exception $e) {
    // Bắt các lỗi không lường trước
    $_SESSION['message'] = "Lỗi hệ thống: " . $e->getMessage();
}

// 6. SAU KHI XONG, CHUYỂN HƯỚNG TRỞ LẠI TRANG QUẢN LÝ
header("Location: manage_rooms.php");
exit();
?>