<?php
header('Content-Type: application/json');

// Kết nối DB
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../controller/RoomController.php');

if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "message" => "Thiếu ID phòng"]);
    exit;
}

$room_id = intval($_GET['id']);

$roomController = new RoomController($conn);
$room = $roomController->getRoomDetailsById($room_id);

if (!$room) {
    echo json_encode(["success" => false, "message" => "Không tìm thấy thông tin phòng"]);
    exit;
}

// Tạo HTML để đưa vào modal
$html = "<p><strong>Loại phòng:</strong> {$room['room_type_name']}</p>";
$html .= "<p><strong>Giá cơ bản:</strong> {$room['room_type_price']} VNĐ</p>";
$html .= "<p><strong>Mô tả:</strong> {$room['room_type_description']}</p>";

if ($room['status'] === 'occupied' && isset($room['guest_name'])) {
    $html .= "<hr>";
    $html .= "<p><strong>Khách hiện tại:</strong> {$room['guest_name']}</p>";
    $html .= "<p><strong>Dự kiến trả phòng:</strong> {$room['expected_checkout']}</p>";
} else {
    $html .= "<hr><p><strong>Không tìm thấy thông tin booking!</strong></p>";
}

echo json_encode([
    "success" => true,
    "html" => $html
]);
?>
