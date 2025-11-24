<?php
// BƯỚC 1: CỔNG BẢO VỆ CUSTOMER
include_once('auth_customer.php'); 

// BƯỚC 2: KHAI BÁO BIẾN LAYOUT
$page_title = "Đặt Phòng Khách sạn";
$module = 'rooms'; 

// 3. GỌI CONFIG VÀ CONTROLLER
include_once(__DIR__ . '/../config.php'); 
include_once(__DIR__ . '/../controller/RoomController.php'); 

// 4. KHỞI TẠO VÀ LẤY DỮ LIỆU PHÒNG TRỐNG (ĐÃ CHỈNH SỬA)
$roomController = new RoomController($conn);

// 4a. Lấy tham số tìm kiếm từ URL ($_GET)
// Dữ liệu từ JS sẽ là YYYY-MM-DD. Lấy trực tiếp.
$check_in = isset($_GET['check_in']) ? $_GET['check_in'] : null;
$check_out = isset($_GET['check_out']) ? $_GET['check_out'] : null;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : null;

// Chuyển đổi sang định dạng hiển thị dd/mm/yyyy (để dùng trong HTML data và hiển thị)
$check_in_str = $check_in ? date('d/m/Y', strtotime($check_in)) : null;
$check_out_str = $check_out ? date('d/m/Y', strtotime($check_out)) : null;

// 4b. Gọi hàm với tham số (sử dụng $check_in, $check_out ở định dạng YYYY-MM-DD)
$available_rooms = $roomController->getAvailableRooms($check_in, $check_out, $max_price);

// 5. LẤY THÔNG BÁO (nếu có)
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); 

// 6. GỌI HEADER
include_once('../layout/user/header_user.php');
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* Lightweight visual polish for a cleaner, more premium look */
        .room-card { border: 0; border-radius: 12px; overflow: hidden; }
        .room-img { height:160px; background: linear-gradient(135deg,#eef2ff 0%,#ffffff 100%); display:flex; align-items:center; justify-content:center; color:#6c63ff; font-size:42px; }
        .room-badge { position:absolute; top:12px; left:12px; background:rgba(0,0,0,0.6); color:#fff; padding:6px 10px; border-radius:8px; font-size:13px; }
        .room-features { font-size:13px; color:#6c757d; }
        .hero-rooms { background:#f8f9ff; border-radius:12px; padding:24px; margin-bottom:18px; }
        .price-large { font-size:1.15rem; }
        @media (max-width:576px) { .room-img { height:120px; font-size:36px; } }
    </style>

    <div class="container mt-5">
        <div class="hero-rooms d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h1 class="mb-1"><i class="fa fa-bed text-primary"></i> Chọn Phòng & Đặt</h1>
                <p class="text-muted mb-0">Xem phòng trống, so sánh giá và đặt nhanh chóng.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="../index.php" class="btn btn-outline-primary">
                    <i class="fa fa-home me-1"></i> Về trang chủ
                </a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Lỗi') !== false ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

            <div class="row align-items-center mb-4">
                <div class="col-md-12 text-center text-md-start">
                    <p class="mb-0 fw-bold">
                        <?php
                        // Hiển thị thông tin tìm kiếm
                        $search_info = "Phòng trống";
                        if ($check_in_str && $check_out_str) {
                            $search_info .= " từ <span class='text-primary'>{$check_in_str}</span> đến <span class='text-primary'>{$check_out_str}</span>";
                        }
                        if ($max_price) {
    $formatted_price = number_format($max_price, 0, ',', '.');
    // Sửa thành "tối đa"
    $search_info .= " với giá tối đa <span class='text-danger'>{$formatted_price} VNĐ/đêm</span>";
}
else {
                            if (!$check_in_str) $search_info .= " từ hôm nay";
                        }
                        echo $search_info . ":";
                        ?>
                    </p>
                </div>
        </div>

        <div id="rooms-list" class="row g-4">
            <?php if (empty($available_rooms)): ?>
                <div class="col-12">
                    <div class="card p-4 text-center">
                        <h5 class="mb-2">Không có phòng phù hợp 😥</h5>
                        <p class="text-muted mb-3">
                            Hiện tại không có phòng nào thỏa mãn điều kiện tìm kiếm của bạn. 
                            <?php 
                            if ($check_in_str || $max_price) {
                                echo "Vui lòng <a href='../index.php' class='fw-bold'>thử lại với ngày khác hoặc mức giá linh hoạt hơn</a>.";
                            }
                            ?>
                        </p>
                        <a href="../index.php" class="btn btn-primary">Tìm kiếm lại</a>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($available_rooms as $room): ?>
                    <div class="col-sm-6 col-lg-4">
                        <div class="card room-card shadow-sm h-100 position-relative">
                            <div class="room-img position-relative">
                                <div class="room-badge">Phòng #<?php echo htmlspecialchars($room['room_number']); ?></div>
                                <i class="fa fa-hotel"></i>
                            </div>
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title mb-1 fw-semibold"><?php echo htmlspecialchars($room['type_name']); ?></h5>
                                <p class="room-features mb-2 small"><?php echo htmlspecialchars($room['description']); ?></p>

                                <div class="mt-auto d-flex align-items-center justify-content-between">
                                    <div>
                                        <div class="text-muted small">Giá bắt đầu</div>
                                        <div class="fw-bold text-danger price-large"><?php echo number_format($room['base_price'], 0, ',', '.'); ?> VNĐ / đêm</div>
                                    </div>

                                    <div class="text-end">
                                        <button type="button" 
                                            class="btn btn-primary btn-book-room"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#bookingModal"
                                            data-room-id="<?php echo $room['id']; ?>"
                                            data-room-number="<?php echo $room['room_number']; ?>"
                                            data-room-price="<?php echo $room['base_price']; ?>"
                                            data-check-in="<?php echo htmlspecialchars($check_in_str ?? ''); ?>"
                                            data-check-out="<?php echo htmlspecialchars($check_out_str ?? ''); ?>"> 
                                            <i class="fa fa-calendar-plus me-1"></i> Đặt ngay
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="modal fade" id="bookingModal" tabindex="-1" aria-labelledby="bookingModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title" id="bookingModalLabel"><i class="fa fa-calendar-alt"></i> Đặt Phòng: <span id="modal_room_number"></span></h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="booking_process.php" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="create_booking">
                <input type="hidden" name="room_id" id="modal_room_id">
                <input type="hidden" name="total_price" id="modal_total_price"> 
                <p class="text-muted">Giá phòng: <span id="modal_room_price_display" class="fw-bold text-danger"></span> / đêm</p>

                <div class="mb-3">
                    <label for="check_in_date" class="form-label fw-semibold">Ngày Check-in:</label>
                    <input type="text" class="form-control bg-white" id="check_in_date" name="check_in_date" placeholder="Chọn ngày nhận phòng" required>
                </div>
                
                <div class="mb-3">
                    <label for="check_out_date" class="form-label fw-semibold">Ngày Check-out:</label>
                    <input type="text" class="form-control bg-white" id="check_out_date" name="check_out_date" placeholder="Chọn ngày trả phòng" required>
                </div>
                
                <div class="alert alert-info mt-3">
                    Tổng tiền tạm tính: <span id="total_price_calculated" class="fw-bold">0 VNĐ</span>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-success">Xác nhận Đặt Phòng</button>
              </div>
          </form>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/vn.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        var bookingModal = document.getElementById('bookingModal');
        var currentRoomPrice = 0;

        // --- 1. CẤU HÌNH FLATPICKR (LỊCH dd/mm/yyyy) ---
        
        var fpCheckIn = flatpickr("#check_in_date", {
            locale: "vn",           
            dateFormat: "Y-m-d",     // Định dạng giá trị gửi đi (định dạng CSDL)
            altInput: true,         
            altFormat: "d/m/Y",      // Định dạng hiển thị
            onChange: function(selectedDates, dateStr, instance) {
                fpCheckOut.set('minDate', dateStr); 
                calculateTotalPrice();
            }
        });

        var fpCheckOut = flatpickr("#check_out_date", {
            locale: "vn",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            onChange: function(selectedDates, dateStr, instance) {
                calculateTotalPrice();
            }
        });

        // --- 2. HÀM TÍNH TOÁN GIÁ (Giữ nguyên) ---
        function calculateTotalPrice() {
            var checkIn = document.getElementById('check_in_date').value