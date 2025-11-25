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

   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/vn.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Khai báo các biến
        var bookingModalEl = document.getElementById('bookingModal');
        var currentRoomPrice = 0;
        
        // --- CẤU HÌNH LỊCH (FLATPICKR) ---
        var fpCheckIn = flatpickr("#check_in_date", {
            locale: "vn",
            dateFormat: "Y-m-d", // Gửi lên server: Năm-Tháng-Ngày
            altInput: true,
            altFormat: "d/m/Y",  // Hiển thị: Ngày/Tháng/Năm
            minDate: "today",
            onChange: function(selectedDates, dateStr, instance) {
                // Khi chọn ngày đến, ngày đi phải lớn hơn ít nhất 1 ngày
                if (selectedDates[0]) {
                    var minOutDate = new Date(selectedDates[0]);
                    minOutDate.setDate(minOutDate.getDate() + 1);
                    fpCheckOut.set('minDate', minOutDate);
                    
                    // Nếu ngày đi hiện tại không hợp lệ, xóa đi
                    if(fpCheckOut.selectedDates[0] && fpCheckOut.selectedDates[0] <= selectedDates[0]){
                         fpCheckOut.clear();
                    }
                }
                calculateTotalPrice();
            }
        });

        var fpCheckOut = flatpickr("#check_out_date", {
            locale: "vn",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            minDate: new Date().fp_incr(1),
            onChange: function(selectedDates, dateStr, instance) {
                calculateTotalPrice();
            }
        });

        // --- SỰ KIỆN KHI MỞ MODAL ---
        // Sử dụng event của Bootstrap để bắt sự kiện mở
        if (bookingModalEl) {
            bookingModalEl.addEventListener('show.bs.modal', function(event) {
                // Nút đã bấm để mở modal
                var button = event.relatedTarget;

                // Lấy dữ liệu từ nút bấm (data-...)
                var roomId = button.getAttribute('data-room-id');
                var roomNumber = button.getAttribute('data-room-number');
                var roomPrice = parseFloat(button.getAttribute('data-room-price'));
                
                // Lấy ngày tìm kiếm trước đó (nếu có)
                var prevCheckIn = button.getAttribute('data-check-in'); 
                var prevCheckOut = button.getAttribute('data-check-out');

                // Cập nhật giao diện Modal
                document.getElementById('modal_room_number').textContent = roomNumber;
                document.getElementById('modal_room_price_display').textContent = new Intl.NumberFormat('vi-VN').format(roomPrice);
                document.getElementById('modal_room_id').value = roomId;
                
                // Lưu giá hiện tại để tính toán
                currentRoomPrice = roomPrice;
                
                // Reset tổng tiền
                updateTotalDisplay(0, 0);

                // Logic điền ngày (nếu cần thiết, ở đây ta ưu tiên reset để khách chọn lại cho đúng)
                // Nếu muốn giữ ngày tìm kiếm, bỏ comment 2 dòng dưới:
                // if(prevCheckIn) fpCheckIn.setDate(prevCheckIn, true); 
                // if(prevCheckOut) fpCheckOut.setDate(prevCheckOut, true);
            });
        }

        // --- HÀM TÍNH TIỀN ---
        // --- HÀM TÍNH TIỀN (Đã nâng cấp tính năng tăng giá cuối tuần) ---
// --- HÀM TÍNH TIỀN (Đã nâng cấp: Chặn > 30 ngày + Tăng giá cuối tuần) ---
function calculateTotalPrice() {
    var checkInDate = fpCheckIn.selectedDates[0];
    var checkOutDate = fpCheckOut.selectedDates[0];
    
    var totalPriceDisplay = document.getElementById('total_price_calculated');
    var totalPriceInput = document.getElementById('modal_total_price');
    
    // Lấy nút Submit để khóa lại nếu vi phạm ngày
    var submitBtn = document.querySelector('#bookingModal button[type="submit"]');

    if (checkInDate && checkOutDate && checkOutDate > checkInDate) {
        // 1. Tính số đêm trước để kiểm tra giới hạn
        var diffTime = Math.abs(checkOutDate - checkInDate);
        var nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        // --- KIỂM TRA: NẾU QUÁ 30 NGÀY ---
        if (nights > 30) {
            // Hiển thị cảnh báo màu đỏ
            totalPriceDisplay.innerHTML = '<span class="text-danger fw-bold"><i class="fa fa-phone"></i> Thời gian lưu trú quá 30 ngày. Vui lòng liên hệ trực tiếp để nhận ưu đãi dài hạn!</span>';
            totalPriceInput.value = 0;
            
            // Khóa nút xác nhận đặt phòng
            submitBtn.disabled = true; 
            return; // Dừng hàm, không tính tiền nữa
        }

        // Nếu hợp lệ (< 30 ngày), mở lại nút Submit
        submitBtn.disabled = false;

        // --- BẮT ĐẦU TÍNH TIỀN (LOGIC CŨ) ---
        var total = 0;
        var surchargeCount = 0; // Đếm số đêm cuối tuần
        
        // Tạo biến chạy loop
        var currentDate = new Date(checkInDate);
        // Loop chạy theo số đêm (nights) đã tính ở trên
        for (var i = 0; i < nights; i++) {
            var dayOfWeek = currentDate.getDay(); // 0: CN, 6: T7
            
            // Nếu là T7 hoặc CN -> Tăng 10%
            if (dayOfWeek === 6 || dayOfWeek === 0) {
                total += currentRoomPrice * 1.1; 
                surchargeCount++;
            } else {
                total += currentRoomPrice;
            }
            
            // Tăng ngày lên để check ngày tiếp theo
            currentDate.setDate(currentDate.getDate() + 1);
        }

        // Làm tròn tổng tiền
        total = Math.round(total);

        // --- HIỂN THỊ KẾT QUẢ ---
        // Tạo text hiển thị cơ bản
        var displayText = new Intl.NumberFormat('vi-VN').format(total) + " VNĐ (" + nights + " đêm)";
        
        // Nếu có phụ thu cuối tuần, thêm dòng chú thích nhỏ
        if (surchargeCount > 0) {
            var surchargeAmount = (currentRoomPrice * 0.1) * surchargeCount;
            var formattedSurcharge = new Intl.NumberFormat('vi-VN').format(surchargeAmount);
            displayText += ` <br><span class="small text-danger fw-normal fst-italic">(Đã bao gồm ${formattedSurcharge}đ phụ thu cuối tuần)</span>`;
        }

        totalPriceDisplay.innerHTML = displayText;
        totalPriceDisplay.classList.remove('text-muted');
        totalPriceDisplay.classList.add('text-success');
        
        // Gán vào input hidden để gửi đi
        totalPriceInput.value = total;

    } else {
        // Trường hợp chưa chọn ngày hợp lệ
        totalPriceDisplay.textContent = "0 VNĐ";
        totalPriceDisplay.classList.add('text-muted');
        totalPriceInput.value = 0;
        if(submitBtn) submitBtn.disabled = false; // Mặc định cứ mở nút
    }
}

        // Hàm cập nhật hiển thị giá
        function updateTotalDisplay(amount, nights) {
            var displayEl = document.getElementById('total_price_calculated');
            var inputEl = document.getElementById('modal_total_price');
            
            if(amount > 0){
                displayEl.textContent = new Intl.NumberFormat('vi-VN').format(amount) + " VNĐ (" + nights + " đêm)";
                displayEl.classList.remove('text-muted');
                displayEl.classList.add('text-success');
            } else {
                displayEl.textContent = "0 VNĐ";
                displayEl.classList.add('text-muted');
                displayEl.classList.remove('text-success');
            }
            inputEl.value = amount;
        }
    });
</script>