<?php
// BƯỚC 1: CỔNG BẢO VỆ CUSTOMER
include_once('auth_customer.php'); 

// BƯỚC 2: KHAI BÁO BIẾN LAYOUT
$page_title = "Đặt Phòng Khách sạn";
$module = 'rooms'; 

// 3. GỌI CONFIG VÀ CONTROLLER
include_once(__DIR__ . '/../config.php'); 
include_once(__DIR__ . '/../controller/RoomController.php'); 

// 4. KHỞI TẠO VÀ LẤY DỮ LIỆU PHÒNG TRỐNG
$roomController = new RoomController($conn);
$available_rooms = $roomController->getAvailableRooms();

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
            <div class="col-md-6 col-lg-5 mb-2 mb-md-0">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa fa-search"></i></span>
                    <input type="text" class="form-control" placeholder="Tìm theo số phòng hoặc loại (ví dụ: Deluxe)">
                </div>
            </div>
            <div class="col-md-6 col-lg-7 text-md-end">
                <small class="text-muted">Bạn có thể chọn ngày khi nhấn "Đặt ngay" trên từng phòng.</small>
            </div>
        </div>

        <div id="rooms-list" class="row g-4">
            <?php if (empty($available_rooms)): ?>
                <div class="col-12">
                    <div class="card p-4 text-center">
                        <h5 class="mb-2">Không có phòng trống</h5>
                        <p class="text-muted mb-3">Hiện tại không còn phòng trống để đặt. Bạn có thể trở về trang chủ hoặc thử thay đổi ngày.</p>
                        <a href="../index.php" class="btn btn-primary">Về trang chủ</a>
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
                                        <div class="fw-bold text-danger price-large"><?php echo number_format($room['base_price'], 0, ',', '.'); ?> VNĐ</div>
                                    </div>

                                    <div class="text-end">
                                        <button type="button" 
                                                class="btn btn-primary btn-book-room"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#bookingModal"
                                                data-room-id="<?php echo $room['id']; ?>"
                                                data-room-number="<?php echo $room['room_number']; ?>"
                                                data-room-price="<?php echo $room['base_price']; ?>">
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
            dateFormat: "Y-m-d",      
            altInput: true,           
            altFormat: "d/m/Y",       
            // minDate: "today",      <-- ĐÃ XÓA DÒNG NÀY (Để hiện ngày quá khứ)
            onChange: function(selectedDates, dateStr, instance) {
                // Logic cũ: Khi chọn Check-in thì Check-out phải lớn hơn hoặc bằng
                // Chúng ta vẫn giữ logic này để tránh chọn ngày trả trước ngày nhận
                fpCheckOut.set('minDate', dateStr); 
                calculateTotalPrice();
            }
        });

        var fpCheckOut = flatpickr("#check_out_date", {
            locale: "vn",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            // minDate: "today",      <-- ĐÃ XÓA DÒNG NÀY
            onChange: function(selectedDates, dateStr, instance) {
                calculateTotalPrice();
            }
        });

        // --- 2. HÀM TÍNH TOÁN GIÁ (Giữ nguyên) ---
        function calculateTotalPrice() {
            var checkIn = document.getElementById('check_in_date').value;
            var checkOut = document.getElementById('check_out_date').value;

            if (checkIn && checkOut) {
                var date1 = new Date(checkIn);
                var date2 = new Date(checkOut);
                var timeDiff = date2.getTime() - date1.getTime();
                var dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)); 

                if (dayDiff > 0) {
                    var totalPrice = dayDiff * currentRoomPrice;
                    document.getElementById('total_price_calculated').textContent = new Intl.NumberFormat('vi-VN').format(totalPrice) + ' VNĐ';
                    document.getElementById('modal_total_price').value = totalPrice;
                    return;
                }
            }
            document.getElementById('total_price_calculated').textContent = '0 VNĐ';
            document.getElementById('modal_total_price').value = 0;
        }

        // --- 3. XỬ LÝ KHI MỞ MODAL (Giữ nguyên) ---
        bookingModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget; 
            var roomId = button.getAttribute('data-room-id');
            var roomNumber = button.getAttribute('data-room-number');
            var roomPrice = parseFloat(button.getAttribute('data-room-price'));

            currentRoomPrice = roomPrice;

            document.getElementById('modal_room_number').textContent = roomNumber;
            document.getElementById('modal_room_id').value = roomId;
            document.getElementById('modal_room_price_display').textContent = new Intl.NumberFormat('vi-VN').format(roomPrice);

            // Reset lịch
            fpCheckIn.clear();
            fpCheckOut.clear();
            document.getElementById('total_price_calculated').textContent = '0 VNĐ';
        });
    });
    
    // ... (Đoạn code hiển thị Popup SweetAlert giữ nguyên) ...
</script>

<?php
// GỌI FOOTER
include_once('../layout/user/footer_user.php');
?>