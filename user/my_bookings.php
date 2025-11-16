<?php
// BƯỚC 1: CỔNG BẢO VỆ
include_once('auth_customer.php'); 
// $customer_id và $customer_username được lấy từ auth_customer.php

// BƯỚC 2: KHAI BÁO BIẾN LAYOUT
$page_title = "Đơn Đặt Phòng Của Tôi";
$module = 'my_bookings'; 

// 3. GỌI CONFIG VÀ CONTROLLER
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../controller/BookingController.php'); 

// 4. KHỞI TẠO VÀ LẤY DỮ LIỆU THẬT
$bookingController = new BookingController($conn);

// Lấy danh sách đặt phòng CỦA NGƯỜI DÙNG NÀY (sử dụng $customer_id từ auth_customer.php)
$my_bookings = $bookingController->getUserBookings($customer_id);

// 5. LẤY THÔNG BÁO (nếu có) TỪ TỆP PROCESS
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); 

// 6. GỌI HEADER
include_once('../layout/user/header_user.php');
?>

    <div class="container mt-5">
        <h1 class="mb-4"><i class="fa fa-book"></i> Đơn Đặt Phòng Của Tôi</h1>
        <p class="text-muted">Chào mừng, **<?php echo htmlspecialchars($customer_username); ?>**.</p>
        
        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Lỗi') !== false ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Danh sách đơn đã đặt</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Phòng</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($my_bookings)): ?>
                            <tr>
                                <td colspan="6" class="text-center">Bạn chưa có đơn đặt phòng nào. 
                                    <a href="rooms.php" class="text-primary fw-bold">Đặt phòng ngay!</a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($my_bookings as $booking): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($booking['room_number']); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($booking['check_in_date'])); ?></td>
                                    <td><?php echo date('Y-m-d', strtotime($booking['check_out_date'])); ?></td>
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
                                    <td>
                                        <?php 
                                            $status = $booking['status'];
                                            if ($status == 'pending'): // Chỉ hiện nút thanh toán khi đơn đang chờ
                                        ?>
                                        
                                            <form action="booking_process.php" method="POST" style="display:inline;" class="me-2">
                                                <input type="hidden" name="action" value="process_payment_simulate">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <input type="hidden" name="amount" value="<?php echo $booking['total_price']; ?>"> 

                                                <button type="submit" class="btn btn-warning btn-sm">
                                                    <i class="fa fa-credit-card me-1"></i> Thanh toán 
                                                </button>
                                            </form>

                                            <form action="booking_process.php" method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy đơn này?');" style="display:inline;">
                                                <input type="hidden" name="action" value="delete_booking">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-trash"></i> Hủy
                                                </button>
                                            </form>

                                        <?php elseif ($status == 'confirmed'): ?>
                                            <span class="badge bg-primary"><i class="fa fa-check"></i> Đã xác nhận</span>
                                            <form action="booking_process.php" method="POST" onsubmit="return confirm('Đơn đã xác nhận. Bạn có chắc muốn hủy?');" style="display:inline;">
                                                <input type="hidden" name="action" value="delete_booking">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fa fa-trash"></i> Hủy
                                                </button>
                                            </form>
                                        <?php elseif ($status == 'checked_in'): ?>
                                            <span class="badge bg-success"><i class="fa fa-bed"></i> Đã nhận phòng</span>
                                        <?php elseif ($status == 'checked_out'): ?>
                                            <span class="badge bg-info"><i class="fa fa-sign-out"></i> Đã trả phòng</span>
                                        <?php elseif ($status == 'cancelled'): ?>
                                            <span class="badge bg-secondary"><i class="fa fa-times"></i> Đã hủy</span>
                                        <?php else: ?>
                                            <span class="text-muted small">Không có hành động</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
<?php
// GỌI FOOTER
include_once('../layout/user/footer_user.php');
?>