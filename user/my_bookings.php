<?php
// FILE: user/my_bookings.php

// 1. CỔNG BẢO VỆ & CONFIG
include_once('auth_customer.php'); 
include_once(__DIR__ . '/../config.php');
include_once(__DIR__ . '/../controller/BookingController.php'); 

$page_title = "Đơn Đặt Phòng Của Tôi";
$module = 'my_bookings'; 

// 2. LẤY DỮ LIỆU
$bookingController = new BookingController($conn);
$my_bookings = $bookingController->getUserBookings($customer_id);

// 3. GỌI HEADER
include_once('../layout/user/header_user.php');
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container mt-5 mb-5">
    <h2 class="mb-4 text-primary"><i class="fa fa-history"></i> Lịch Sử Đặt Phòng</h2>
    <p class="text-muted">Xin chào, <strong><?php echo htmlspecialchars($customer_username); ?></strong>. Dưới đây là danh sách các đơn đặt phòng của bạn.</p>
    
    <div class="card shadow border-0">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0 text-secondary">Danh sách đơn hàng</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Mã đơn</th>
                            <th>Phòng</th>
                            <th>Thời gian lưu trú</th>
                            <th>Tổng tiền</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($my_bookings)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted mb-3"><i class="fa fa-inbox fa-3x"></i></div>
                                    <h6 class="text-muted">Bạn chưa có đơn đặt phòng nào.</h6>
                                    <a href="../index.php" class="btn btn-primary mt-2">
                                        <i class="fa fa-search"></i> Tìm phòng ngay
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($my_bookings as $booking): ?>
                                <tr>
                                    <td class="ps-4 fw-bold">#<?php echo $booking['id']; ?></td>
                                    <td class="text-primary fw-bold">
                                        <?php echo htmlspecialchars($booking['room_number']); ?>
                                    </td>
                                    <td>
                                        <small class="text-muted">Nhận:</small> <strong><?php echo date('d/m/Y', strtotime($booking['check_in_date'])); ?></strong><br>
                                        <small class="text-muted">Trả:&nbsp;&nbsp;&nbsp;</small> <strong><?php echo date('d/m/Y', strtotime($booking['check_out_date'])); ?></strong>
                                    </td>
                                    <td class="fw-bold text-danger">
                                        <?php echo number_format($booking['total_price'], 0, ',', '.'); ?> đ
                                    </td>
                                    <td>
                                        <?php 
                                            $st = $booking['status'];
                                            $badgeClass = 'bg-secondary';
                                            $statusText = 'Không rõ';
                                            
                                            switch ($st) {
                                                case 'pending':
                                                    $badgeClass = 'bg-warning text-dark';
                                                    $statusText = 'Chờ thanh toán';
                                                    break;
                                                case 'confirmed':
                                                    $badgeClass = 'bg-primary';
                                                    $statusText = 'Đã xác nhận';
                                                    break;
                                                case 'checked_in':
                                                    $badgeClass = 'bg-success';
                                                    $statusText = 'Đang ở';
                                                    break;
                                                case 'checked_out':
                                                    $badgeClass = 'bg-info text-dark';
                                                    $statusText = 'Đã trả phòng';
                                                    break;
                                                case 'cancelled':
                                                    $badgeClass = 'bg-danger';
                                                    $statusText = 'Đã hủy';
                                                    break;
                                            }
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?> rounded-pill px-3 py-2">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($st == 'pending'): ?>
                                            <form action="booking_process.php" method="POST" style="display:inline-block;">
                                                <input type="hidden" name="action" value="process_payment_simulate">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <input type="hidden" name="amount" value="<?php echo $booking['total_price']; ?>"> 
                                                <button type="submit" class="btn btn-warning btn-sm shadow-sm" title="Thanh toán ngay">
                                                    <i class="fa fa-credit-card"></i> Trả tiền
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($st == 'pending' || $st == 'confirmed'): ?>
                                            <form action="booking_process.php" method="POST" style="display:inline-block;" class="frm-cancel">
                                                <input type="hidden" name="action" value="delete_booking">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <button type="button" class="btn btn-outline-danger btn-sm btn-cancel" title="Hủy đơn">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </form>
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
</div>

<?php include_once('../layout/user/footer_user.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<?php
if (isset($_SESSION['swal_message']) && isset($_SESSION['swal_type'])) {
    $msg = $_SESSION['swal_message'];
    $type = $_SESSION['swal_type']; // success hoặc warning
?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: '<?php echo $type; ?>', // warning (màu vàng) hoặc success (màu xanh)
                title: 'Thông báo',
                html: '<?php echo $msg; ?>', 
                // Nếu là warning (>15 ngày), tin nhắn sẽ là: "...vui lòng thanh toán trong 24h..."
                confirmButtonText: 'Đã hiểu',
                confirmButtonColor: '#3085d6'
            });
        });
    </script>
<?php
    unset($_SESSION['swal_message']);
    unset($_SESSION['swal_type']);
}
?>
<script>
    // 1. Xác nhận trước khi Hủy
    document.querySelectorAll('.btn-cancel').forEach(button => {
        button.addEventListener('click', function() {
            Swal.fire({
                title: 'Bạn chắc chắn chứ?',
                text: "Đơn đặt phòng này sẽ bị hủy vĩnh viễn!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Vâng, hủy đơn!',
                cancelButtonText: 'Giữ lại'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.closest('form').submit();
                }
            });
        });
    });

    // 2. Hiển thị thông báo từ Server (Backend gửi sang)
    <?php
    if (isset($_SESSION['swal_message']) && isset($_SESSION['swal_type'])) {
        $msg = $_SESSION['swal_message'];
        $type = $_SESSION['swal_type']; // success, error, warning
    ?>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: '<?php echo $type; ?>',
                title: 'Thông báo',
                html: '<?php echo $msg; ?>', 
                confirmButtonText: 'Đã hiểu',
                confirmButtonColor: '#3085d6'
            });
        });
    <?php
        // Xóa session để không hiện lại khi F5
        unset($_SESSION['swal_message']);
        unset($_SESSION['swal_type']);
    }
    ?>
</script>