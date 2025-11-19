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

    <div class="container mt-5">
        <h1 class="mb-4"><i class="fa fa-bed"></i> Chọn Phòng & Đặt</h1>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Lỗi') !== false ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <?php if (empty($available_rooms)): ?>
                <div class="col-12">
                    <div class="alert alert-warning">
                        Hiện tại không còn phòng nào trống để đặt. Vui lòng thử lại sau.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($available_rooms as $room): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title fw-bold text-primary"><?php echo htmlspecialchars($room['room_number']); ?> - <?php echo htmlspecialchars($room['type_name']); ?></h5>
                                <p class="card-text text-muted small"><?php echo htmlspecialchars($room['description']); ?></p>
                                <p class="fs-4 fw-bold mt-auto text-danger">
                                    <?php echo number_format($room['base_price'], 0, ',', '.'); ?> VNĐ / đêm
                                </p>
                                <button type="button" 
                                        class="btn btn-success btn-sm mt-2 btn-book-room" 
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