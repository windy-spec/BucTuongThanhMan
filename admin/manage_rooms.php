<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// Tạm thời dùng session_start() để kiểm tra Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
     header("Location: ../login/index.php"); 
     exit();
}

// 1. KHAI BÁO BIẾN LAYOUT
$page_title = "Quản lý Phòng Khách sạn";
$module = 'quan_ly_phong'; 

// 2. GỌI CONFIG VÀ CONTROLLER
include_once(__DIR__ . '/../config.php'); 
include_once(__DIR__ . '/../controller/RoomController.php'); 

// 3. KHỞI TẠO CONTROLLER
$roomController = new RoomController($conn);

// 4. LẤY DỮ LIỆU ĐỂ HIỂN THỊ
$all_rooms = $roomController->getAllRoomsWithTypes();
$room_types = $roomController->getAllRoomTypes(); // Cho dropdown trong form/modal

// 5. LẤY THÔNG BÁO (nếu có) TỪ TỆP PROCESS
$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']); 

// 6. GỌI HEADER (Mở HTML, Navbar)
include_once('../layout/admin/header_admin.php');
?>

    <div class="container mt-5">
        <h1 class="mb-4"><i class="fa fa-door-open"></i> Quản lý Phòng</h1>

        <?php if ($message): ?>
            <div class="alert <?php echo strpos($message, 'Lỗi') !== false ? 'alert-danger' : 'alert-success'; ?> alert-dismissible fade show" role="alert">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Thêm phòng mới</h5>
            </div>
            <div class="card-body">
                <form action="room_process.php" method="POST" class="row g-3">
                    
                    <input type="hidden" name="action" value="create_room">
                    
                    <div class="col-md-5">
                        <label for="room_number" class="form-label fw-semibold">Số phòng:</label>
                        <input type="text" class="form-control" id="room_number" name="room_number" placeholder="Vd: P101, P202..." required>
                    </div>
                    <div class="col-md-5">
                        <label for="room_type_id" class="form-label fw-semibold">Loại phòng:</label>
                        <select class="form-select" id="room_type_id" name="room_type_id" required>
                            <option value="">-- Chọn loại phòng --</option>
                            <?php foreach ($room_types as $type): ?>
                                <option value="<?php echo $type['id']; ?>">
                                    <?php echo htmlspecialchars($type['type_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa fa-plus"></i> Thêm
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="mb-0">Danh sách phòng hiện tại</h5>
            </div>
            <div class="card-body">
                <table class="table table-hover table-bordered">
                    <thead class="table-dark">
                        <tr>
                            <th>Số phòng</th>
                            <th>Loại phòng</th>
                            <th>Giá cơ bản</th>
                            <th>Trạng thái</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($all_rooms)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Chưa có phòng nào.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_rooms as $room): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($room['room_number']); ?></td>
                                    <td><?php echo htmlspecialchars($room['type_name']); ?></td>
                                    <td><?php echo number_format($room['base_price'], 0, ',', '.'); ?> VNĐ</td>
                                    <td>
                                        <?php 
                                            $status = $room['status'];
                                            $badge_class = 'bg-secondary';
                                            if ($status == 'available') $badge_class = 'bg-success';
                                            if ($status == 'occupied') $badge_class = 'bg-warning text-dark';
                                            if ($status == 'maintenance') $badge_class = 'bg-danger';
                                            if ($status == 'cleaning') $badge_class = 'bg-info text-dark';
                                        ?>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($status); ?></span>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-info btn-sm me-2 text-white btn-edit-room" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editRoomModal"
                                                data-id="<?php echo $room['id']; ?>"
                                                data-number="<?php echo $room['room_number']; ?>"
                                                data-type="<?php echo $room['room_type_id']; ?>" 
                                                data-status="<?php echo $room['status']; ?>">
                                            <i class="fa fa-edit"></i> Sửa
                                        </button>
                                        
                                        <form action="room_process.php" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa phòng này?');" style="display:inline;">
                                            <input type="hidden" name="action" value="delete_room">
                                            <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="fa fa-trash"></i> Xóa
                                            </button>
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
    
    <div class="modal fade" id="editRoomModal" tabindex="-1" aria-labelledby="editRoomModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="editRoomModalLabel"><i class="fa fa-edit"></i> Sửa Thông Tin Phòng</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <form action="room_process.php" method="POST">
              <div class="modal-body">
                <input type="hidden" name="action" value="update_room">
                <input type="hidden" name="room_id" id="edit_room_id">

                <div class="mb-3">
                    <label for="edit_room_number" class="form-label fw-semibold">Số phòng:</label>
                    <input type="text" class="form-control" id="edit_room_number" disabled>
                </div>

                <div class="mb-3">
                    <label for="room_type_id_edit" class="form-label fw-semibold">Loại phòng:</label>
                    <select class="form-select" id="room_type_id_edit" name="room_type_id_edit" required>
                        <option value="">-- Chọn loại phòng --</option>
                        <?php foreach ($room_types as $type): ?>
                            <option value="<?php echo $type['id']; ?>">
                                <?php echo htmlspecialchars($type['type_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="status_edit" class="form-label fw-semibold">Trạng thái:</label>
                    <select class="form-select" id="status_edit" name="status_edit" required>
                        <option value="available">available (Còn trống)</option>
                        <option value="occupied">occupied (Có khách)</option>
                        <option value="cleaning">cleaning (Đang dọn dẹp)</option>
                        <option value="maintenance">maintenance (Bảo trì)</option>
                    </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="submit" class="btn btn-primary">Lưu Thay Đổi</button>
              </div>
          </form>
        </div>
      </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var editModal = document.getElementById('editRoomModal');
            editModal.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget; 
                var roomNumber = button.getAttribute('data-number');
                var roomId = button.getAttribute('data-id');
                var roomType = button.getAttribute('data-type'); // Lấy ID loại phòng
                var roomStatus = button.getAttribute('data-status');

                editModal.querySelector('.modal-title').textContent = 'Sửa Phòng: ' + roomNumber;
                editModal.querySelector('#edit_room_id').value = roomId;
                editModal.querySelector('#edit_room_number').value = roomNumber;
                
                // Đặt giá trị cho dropdowns
                editModal.querySelector('#room_type_id_edit').value = roomType;
                editModal.querySelector('#status_edit').value = roomStatus;
            });
        });
    </script>

<?php
// BƯỚC 7: GỌI FOOTER
include_once('../layout/admin/footer_admin.php');
?>