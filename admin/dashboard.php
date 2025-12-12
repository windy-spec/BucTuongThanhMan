<?php
// BƯỚC 1: CỔNG BẢO VỆ ADMIN
include_once('auth_admin.php'); 

// BƯỚC 2: KHAI BÁO BIẾN LAYOUT
$page_title = "Dashboard Quản lý Khách sạn";
$module = 'dashboard'; 

// BƯỚC 3: GỌI CONFIG VÀ CÁC CONTROLLER MỚI
// Sử dụng __DIR__ để đảm bảo đường dẫn tuyệt đối
include_once(__DIR__ . '/../config.php'); 
include_once(__DIR__ . '/../controller/RoomController.php'); 
include_once(__DIR__ . '/../controller/BookingController.php'); 

// BƯỚC 4: KHỞI TẠO VÀ LẤY DỮ LIỆU THẬT
$roomController = new RoomController($conn);
$bookingController = new BookingController($conn);

// 4a. Lấy Thống kê Card (từ RoomController)
$stats = $roomController->getRoomStats();
$total_rooms = $stats['total'] ?? 0;
$available_rooms = $stats['available'] ?? 0;
$occupied_rooms = $stats['occupied'] ?? 0;
$cleaning_rooms = $stats['cleaning'] ?? 0;

// 4b. Lấy Danh sách Đặt phòng gần đây (từ BookingController)
$recent_bookings = $bookingController->getRecentBookings();

// 4c. Lấy Sơ đồ Trạng thái Phòng (từ BookingController)
$room_status_map = $bookingController->getRoomStatusMap();

// BƯỚC 5: GỌI HEADER (Sử dụng đường dẫn mới)
include_once('../layout/admin/header_admin.php');

// Hàm trợ giúp để ánh xạ trạng thái sang CSS và Icon
function get_status_data($status) {
    $map = [
        'available' => ['class' => 'room-available', 'icon' => 'fa-check', 'text' => 'Trống'],
        'occupied' => ['class' => 'room-occupied', 'icon' => 'fa-bed', 'text' => 'Có khách'],
        'cleaning' => ['class' => 'room-cleaning', 'icon' => 'fa-broom', 'text' => 'Dọn dẹp'],
        'maintenance' => ['class' => 'room-maintenance', 'icon' => 'fa-tools', 'text' => 'Bảo trì'],
    ];
    // Trả về mặc định nếu không khớp
    return $map[$status] ?? ['class' => 'room-unknown', 'icon' => 'fa-question-circle', 'text' => 'Không xác định'];
}

// Hàm trợ giúp cho badge đặt phòng
function get_booking_badge_class($status) {
    return match ($status) {
        'checked_in' => 'bg-success',
        'confirmed' => 'bg-primary',
        'pending' => 'bg-warning text-dark',
        'checked_out' => 'bg-info text-dark',
        'cancelled' => 'bg-danger',
        default => 'bg-secondary',
    };
}
?>

<style>
/* Đã thêm màu mặc định cho trạng thái không xác định */
    .room-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
        gap: 15px;
        padding-top: 10px;
    }
    .room-card {
        padding: 15px;
        border-radius: 8px;
        color: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer; /* Thêm con trỏ để gợi ý có thể click */
    }
    .room-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }
    .room-card .room-number {
        font-size: 1.4rem;
        font-weight: bold;
    }
    .room-card .status-text {
        font-size: 0.8rem;
        opacity: 0.9;
        display: block;
        margin-top: 5px;
    }
    /* Màu sắc */
    .room-available { background-color: #28a945; } /* Green */
    .room-occupied { background-color: #dc3545; } /* Red */
    .room-maintenance { background-color: #6c757d; } /* Gray */
    .room-cleaning { background-color: #ffc107; color: #333 !important; } /* Yellow */
    .room-unknown { background-color: #0d6efd; } /* Blue (Added default) */
</style>

<div class="row g-4 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 text-primary me-3"><i class="fa fa-hotel"></i></div>
                <div>
                    <h5 class="card-title text-muted mb-1">Tổng phòng</h5>
                    <h2 class="card-text fw-bold"><?= $total_rooms; ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 text-success me-3"><i class="fa fa-check-circle"></i></div>
                <div>
                    <h5 class="card-title text-muted mb-1">Còn trống</h5>
                    <h2 class="card-text fw-bold"><?= $available_rooms; ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 text-danger me-3"><i class="fa fa-user-times"></i></div>
                <div>
                    <h5 class="card-title text-muted mb-1">Có khách</h5>
                    <h2 class="card-text fw-bold"><?= $occupied_rooms; ?></h2>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card shadow-sm h-100">
            <div class="card-body d-flex align-items-center">
                <div class="fs-1 text-warning me-3"><i class="fa fa-broom"></i></div>
                <div>
                    <h5 class="card-title text-muted mb-1">Đang dọn</h5>
                    <h2 class="card-text fw-bold"><?= $cleaning_rooms; ?></h2>
                </div>
            </div>
        </div>
    </div>
</div> 

<div class="row g-4 mb-4">
    <div class="col-lg-12"> <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-calendar-alt"></i> Các lượt đặt phòng gần đây</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Khách hàng</th>
                                <th>Phòng</th>
                                <th>Check-in</th>
                                <th>Check-out</th>
                                <th>Trạng thái</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_bookings)): ?>
                                <tr><td colspan="5" class="text-center">Chưa có đơn đặt phòng nào.</td></tr>
                            <?php else: ?>
                                <?php foreach ($recent_bookings as $booking): 
                                    $badge_class = get_booking_badge_class($booking['status']);
                                    $guest_name = htmlspecialchars($booking['guest_name'] ?? 'N/A');
                                    $check_in = date('Y-m-d', strtotime($booking['check_in_date']));
                                    $check_out = date('Y-m-d', strtotime($booking['check_out_date']));
                                    $status_display = match($booking['status']) {
                                        'checked_in' => 'Đã Check-in',
                                        'confirmed' => 'Đã Xác nhận',
                                        'pending' => 'Chờ xử lý',
                                        'checked_out' => 'Đã Check-out',
                                        'cancelled' => 'Đã Hủy',
                                        default => 'Khác',
                                    };
                                ?>
                                <tr>
                                    <td><?= $guest_name; ?></td>
                                    <td><?= htmlspecialchars($booking['room_number']); ?></td>
                                    <td><?= $check_in; ?></td>
                                    <td><?= $check_out; ?></td>
                                    <td>
                                        <span class="badge <?= $badge_class; ?>">
                                            <?= $status_display; ?>
                                        </span>
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
</div> 

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-th-large"></i> Sơ đồ Trạng thái Phòng</h5>
            </div>
            <div class="card-body">
                <div class="room-grid">
    <?php if (empty($room_status_map)): ?>
        <p class="text-center text-muted w-100">Chưa có dữ liệu trạng thái phòng.</p>
    <?php else: ?>
        <?php foreach ($room_status_map as $room): 
            $data = get_status_data($room['status']);
        ?>
        <div class="room-card <?= $data['class']; ?>" 
             data-bs-toggle="modal" 
             data-bs-target="#roomDetailModal" 
             data-room-id="<?= htmlspecialchars($room['id']); ?>"
             data-room-number="<?= htmlspecialchars($room['room_number']); ?>"
             data-room-status-text="<?= $data['text']; ?>"
             style="cursor: pointer;"> 
            <div class="d-flex justify-content-between align-items-center">
                <span class="room-number"><?= htmlspecialchars($room['room_number']); ?></span>
                <i class="fa <?= $data['icon']; ?> fa-lg"></i>
            </div>
            <span class="status-text"><?= $data['text']; ?></span>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="roomDetailModal" tabindex="-1" aria-labelledby="roomDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roomDetailModalLabel">Chi tiết Phòng: <span id="modal-room-number" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>ID Phòng:</strong> <span id="modal-room-id"></span></p>
                <p><strong>Trạng thái:</strong> <span id="modal-room-status"></span></p>
                <hr>
                <div id="modal-dynamic-content">Đang tải thông tin...</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <a href="#" id="modal-action-btn" class="btn btn-primary" style="display:none;">Thực hiện</a>
            </div>
        </div>
    </div>
</div>
</div> 
<script>
    // Đảm bảo Bootstrap và Fetch API (có sẵn trong trình duyệt hiện đại) được sử dụng
    document.addEventListener('DOMContentLoaded', function() {
        const roomDetailModal = document.getElementById('roomDetailModal');
        
        // Lắng nghe sự kiện trước khi Modal hiện ra (Bootstrap 5)
        roomDetailModal.addEventListener('show.bs.modal', function (event) {
            // Lấy phần tử đã kích hoạt modal (thẻ div .room-card)
            const button = event.relatedTarget; 
            
            // Lấy dữ liệu từ data-attributes (đã được bạn thêm vào ở trên)
            const roomId = button.getAttribute('data-room-id');
            const roomNumber = button.getAttribute('data-room-number');
            const roomStatus = button.getAttribute('data-room-status-text');

            // Cập nhật các trường tĩnh trong Modal
            document.getElementById('modal-room-number').textContent = roomNumber;
            document.getElementById('modal-room-id').textContent = roomId;
            document.getElementById('modal-room-status').textContent = roomStatus;
            
            const dynamicContent = document.getElementById('modal-dynamic-content');
            dynamicContent.innerHTML = '<p class="text-info"><i class="fa fa-spinner fa-spin"></i> Đang tải thông tin chi tiết...</p>'; // Trạng thái loading
            
            const actionBtn = document.getElementById('modal-action-btn');
            actionBtn.style.display = 'none';

            // 1. Tải dữ liệu chi tiết bằng Fetch API (AJAX)
            // Cần tạo file /admin/get_room_details.php
            fetch('<?= BASE_URL; ?>admin/get_room_details.php?id=' + roomId) 
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        dynamicContent.innerHTML = data.html;
                        
                        // Cập nhật nút Hành động
                        actionBtn.href = '<?= BASE_URL; ?>admin/manage_rooms.php?id=' + roomId; // Ví dụ: Chuyển đến trang quản lý phòng
                        actionBtn.textContent = 'Quản lý phòng này';
                        actionBtn.style.display = 'inline-block';

                    } else {
                        dynamicContent.innerHTML = '<p class="text-danger">' + data.message + '</p>'; 
                        actionBtn.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Lỗi tải dữ liệu:', error);
                    dynamicContent.innerHTML = '<p class="text-danger"><i class="fa fa-times-circle"></i> Lỗi kết nối hoặc server.</p>';
                });
        });
    });
</script>
<?php
// GỌI FOOTER
include_once('../layout/admin/footer_admin.php');
?>