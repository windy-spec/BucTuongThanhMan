<?php
// 1. KIỂM TRA QUYỀN ADMIN
session_start();
if (!isset($_SESSION['role']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'staff')) {
    header("Location: ../login/index.php");
    exit();
}

// 2. KHAI BÁO BIẾN LAYOUT
$page_title = "Quản lý Đơn Đặt Phòng";
$module = 'quan_ly_don';

// 3. GỌI CONFIG VÀ CONTROLLER
include_once(__DIR__ . '/../config.php'); 
include_once(__DIR__ . '/../controller/BookingController.php'); 

// 4. KHỞI TẠO VÀ LẤY DỮ LIỆU
$bookingController = new BookingController($conn);
$all_bookings = $bookingController->getAllBookings(); // Lấy tất cả đơn

// Các trạng thái có thể có
$status_options = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];

// 5. LẤY THÔNG BÁO
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); 

// 6. GỌI HEADER
include_once('../layout/admin/header_admin.php');
?>

    <div class="container mt-5">
        <h1 class="mb-4"><i class="fa fa-calendar-check"></i> Quản lý Đơn Đặt Phòng</h1>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Lỗi') !== false ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Tổng cộng: <?php echo count($all_bookings); ?> đơn</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-dark">
                            <tr>
                                <th>ID Đơn</th>
                                <th>Phòng</th>
                                <th>Khách hàng</th>
                                <th>Check-in</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Hành động</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_bookings)): ?>
                                <tr>
                                    <td colspan="7" class="text-center">Chưa có đơn đặt phòng nào.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo $booking['id']; ?></td>
                                        <td class="fw-bold"><?php echo htmlspecialchars($booking['room_number']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['guest_name']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($booking['check_in_date'])); ?></td>
                                        <td><?php echo number_format($booking['total_price'], 0, ',', '.'); ?> VNĐ</td>
                                        <td>
                                            <?php 
                                                $status = $booking['status'];
                                                $badge_class = 'bg-secondary';
                                                if ($status == 'confirmed') $badge_class = 'bg-primary';
                                                if ($status == 'pending') $badge_class = 'bg-warning text-dark';
                                                if ($status == 'checked_in') $badge_class = 'bg-success';
                                                if ($status == 'cancelled') $badge_class = 'bg-danger';
                                            ?>
                                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span>
                                        </td>
                                        <td style="width: 250px;">
                                            <form action="booking_process.php" method="POST" style="display:inline-flex; align-items:center;">
                                                <input type="hidden" name="action" value="update_status">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                
                                                <select name="new_status" class="form-select form-select-sm me-2" required>
                                                    <?php foreach ($status_options as $option): ?>
                                                        <option value="<?php echo $option; ?>" <?php echo ($status == $option) ? 'selected' : ''; ?>>
                                                            <?php echo ucfirst($option); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn btn-primary btn-sm">Lưu</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
<?php
// GỌI FOOTER
include_once('../layout/admin/footer_admin.php');
?>  