<?php
// --- PHẦN LOGIC SIÊU CẤP ---

// 1. Định nghĩa thư mục "gốc" (base) mà tụi mình cho phép xem
$base_dir = __DIR__;

// 2. Lấy đường dẫn "con" từ URL (?path=...)
$sub_path = $_GET['path'] ?? ''; // Mặc định là rỗng

// 3. Tạo đường dẫn đầy đủ
$current_path = $base_dir . '/' . $sub_path;

// 4. KIỂM TRA BẢO MẬT (Rất quan trọng)
// Dùng realpath để chuẩn hóa đường dẫn (xóa /../, //, v.v.)
$real_base_dir = realpath($base_dir);
$real_current_path = realpath($current_path);

// Nếu đường dẫn không tồn tại HOẶC nó cố tình đi lùi ra khỏi $base_dir
// strpos(...) !== 0 nghĩa là $real_current_path không bắt đầu bằng $real_base_dir
if ($real_current_path === false || strpos($real_current_path, $real_base_dir) !== 0) {
    // Quay về trang gốc (an toàn)
    $sub_path = '';
    $current_path = $base_dir;
    $real_current_path = $real_base_dir;
}

// 5. Xác định module để "sáng" Sidebar
$module = '';
if (strpos($sub_path, 'bt_khoa') === 0) {
    $module = 'bt_khoa';
} elseif (strpos($sub_path, 'bt_phong') === 0) {
    $module = 'bt_phong';
}

// 6. Đặt Tiêu đề trang
$page_title = "Trình duyệt Bài tập: /" . htmlspecialchars($sub_path);

// 7. Quét thư mục
$folders = [];
$files = [];

// scandir quét toàn bộ nội dung của thư mục
$items = scandir($real_current_path);
foreach ($items as $item) {
    // Bỏ qua '.' và file htaccess (nếu có)
    if ($item == '.' || $item == '.htaccess') {
        continue;
    }
    
    // Bỏ qua '..' (thư mục cha), TRỪ KHI đang ở thư mục con
    if ($item == '..' && $real_current_path == $real_base_dir) {
        continue;
    }

    $item_path = $real_current_path . '/' . $item;
    
    // is_dir kiểm tra xem nó là thư mục hay là file
    if (is_dir($item_path)) {
        $folders[] = $item; // Nếu là thư mục, thêm vào mảng folders
    } else {
        $files[] = $item; // Nếu là file, thêm vào mảng files
    }
}
// --- KẾT THÚC LOGIC ---

// 8. Gọi header (dùng layout Sidebar)
include_once('../includes/header.php');
?>

<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="fa fa-folder-open text-warning"></i> 
            Đang xem: /<?php echo htmlspecialchars($sub_path); ?>
        </h5>
    </div>
    
    <div class="card-body">
        
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th style="width: 50%;">Tên</th>
                        <th>Loại</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Lặp qua MẢNG THƯ MỤC
                    foreach ($folders as $folder) {
                        // Tính đường dẫn MỚI khi bấm vào
                        $new_sub_path = ($sub_path == '' ? '' : $sub_path . '/') . $folder;
                        
                        // Xử lý link cho nút ".." (Đi lùi)
                        if ($folder == '..') {
                            // dirname trả về đường dẫn cha
                            $new_sub_path = dirname($sub_path);
                            // Nếu lùi về gốc thì là rỗng
                            if ($new_sub_path == '.') $new_sub_path = '';
                        }
                        
                        // Tạo link
                        $link = 'bt_list.php?path=' . urlencode($new_sub_path);
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo $link; ?>" class="fw-bold text-decoration-none">
                                <i class="fa fa-folder text-warning me-2"></i>
                                <?php echo htmlspecialchars($folder); ?>
                            </a>
                        </td>
                        <td>Thư mục</td>
                    </tr>
                    <?php } // Hết lặp folder ?>

                    <?php
                    // Lặp qua MẢNG FILE
                    foreach ($files as $file) {
                        // Link trỏ thẳng vào file (không qua bt_list.php nữa)
                        // vì đây là file, không phải thư mục (sẽ không 403)
                        $link = htmlspecialchars($sub_path . '/' . $file);
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo $link; ?>" target="_blank" class="text-decoration-none">
                                <i class="fa fa-file text-muted me-2"></i>
                                <?php echo htmlspecialchars($file); ?>
                            </a>
                        </td>
                        <td>File</td>
                    </tr>
                    <?php } // Hết lặp file ?>

                    <?php
                    // Thông báo nếu thư mục rỗng
                    if (empty($folders) && empty($files)) {
                        echo '<tr><td colspan="2" class="text-center text-muted">Thư mục này rỗng.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div> </div> </div> <?php
    // Gọi footer (dùng layout Sidebar)
    include_once('../includes/footer.php');
?>