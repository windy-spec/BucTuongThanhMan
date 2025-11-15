<?php
// --- PHẦN LOGIC PHP ---
// Chúng ta "giả lập" (fake) dữ liệu ở đây
$rooms = [
    ['id' => 101, 'type' => 'Standard', 'status' => 'Available'],
    ['id' => 102, 'type' => 'Standard', 'status' => 'Occupied'],
    ['id' => 103, 'type' => 'Deluxe',   'status' => 'Available'],
    ['id' => 201, 'type' => 'Suite',    'status' => 'Cleaning'],
    ['id' => 202, 'type' => 'Standard', 'status' => 'Occupied'],
    ['id' => 203, 'type' => 'Deluxe',   'status' => 'Available'],
    ['id' => 301, 'type' => 'Standard', 'status' => 'Available'],
    ['id' => 302, 'type' => 'Suite',    'status' => 'Occupied'],
];

$bookings = [
    ['guest_name' => 'Nguyễn Văn A', 'room_id' => 102, 'check_in' => '2025-11-15', 'check_out' => '2025-11-17'],
    ['guest_name' => 'Trần Thị B', 'room_id' => 202, 'check_in' => '2025-11-14', 'check_out' => '2025-11-16'],
    ['guest_name' => 'Lê Văn C', 'room_id' => 302, 'check_in' => '2025-11-15', 'check_out' => '2025-11-18'],
];

// Hàm trợ giúp để dịch trạng thái
function getStatusClass($status) {
    switch ($status) {
        case 'Available': return 'status-available';
        case 'Occupied':  return 'status-occupied';
        case 'Cleaning':  return 'status-cleaning';
        default: return '';
    }
}

function getStatusText($status) {
    switch ($status) {
        case 'Available': return 'Còn trống';
        case 'Occupied':  return 'Có khách';
        case 'Cleaning':  return 'Dọn dẹp';
        default: return 'Không rõ';
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bảng điều khiển - Quản lý Khách sạn</title>
    
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            color: #333;
        }
        header.main-header {
            background-color: #004a99;
            color: white;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        main.container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        h2 {
            color: #004a99;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
        }
        .room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
        }
        .room-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s;
        }
        .room-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        .room-card .room-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #004a99;
        }
        .room-card .room-type {
            font-size: 0.9rem;
            color: #666;
            margin-top: 5px;
        }
        .room-card .room-status {
            font-size: 0.9rem;
            font-weight: bold;
            margin-top: 10px;
            padding: 5px;
            border-radius: 4px;
        }
        .status-available {
            background-color: #e0f8e0;
            border: 1px solid #00a000;
            color: #006000;
        }
        .status-occupied {
            background-color: #ffe0e0;
            border: 1px solid #d00000;
            color: #a00000;
        }
        .status-cleaning {
            background-color: #fffbe0;
            border: 1px solid #e0c000;
            color: #806000;
        }
        .booking-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        .booking-table th, .booking-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .booking-table thead {
            background-color: #f0f0f0;
        }
        .booking-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

    <header class="main-header">
        <h1>Bảng điều khiển Quản lý Khách sạn</h1>
    </header>

    <main class="container">

        <section class="room-overview">
            <h2>Tổng quan Trạng thái Phòng</h2>
            
            <div class="room-grid">
                <?php
                foreach ($rooms as $room) {
                    $status_class = getStatusClass($room['status']);
                    $status_text = getStatusText($room['status']);
                    
                    // LỖI ĐÃ SỬA: class'...' thành class="..."
                    echo "<div class=\"room-card\">"; 
                    echo "    <div class=\"room-number\">Phòng " . htmlspecialchars($room['id']) . "</div>";
                    echo "    <div class=\"room-type\">" . htmlspecialchars($room['type']) . "</div>";
                    // LỖI ĐÃ SỬA: class'...' thành class="..."
                    echo "    <div class=\"room-status " . $status_class . "\">" . $status_text . "</div>";
                    echo "</div>";
                }
                ?>
            </div>
        </section>

        <section class="booking-list">
            <h2>Danh sách Đặt phòng Hiện tại</h2>

            <table class="booking-table">
                <thead>
                    <tr>
                        <th>Tên khách hàng</th>
                        <th>Số phòng</th>
                        <th>Ngày nhận phòng (Check-in)</th>
                        <th>Ngày trả phòng (Check-out)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($bookings as $booking) {
                        echo "<tr>";
                        echo "    <td>" . htmlspecialchars($booking['guest_name']) . "</td>";
                        // LỖI ĐÃ SỬA: Xóa "D" bị thừa
                        echo "    <td>" . htmlspecialchars($booking['room_id']) . "</td>";
                        echo "    <td>" . htmlspecialchars($booking['check_in']) . "</td>";
                        echo "    <td>" . htmlspecialchars($booking['check_out']) . "</td>";
                        echo "</tr>";
                    }

                    if (empty($bookings)) {
                        echo "<tr><td colspan='4'>Không có lượt đặt phòng nào hiện tại.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </section>

    </main>

</body>
</html>