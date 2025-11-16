<?php
    // GỌI CONFIG (Lùi 2 cấp: admin -> layout -> QLKS)
    // Sửa đường dẫn này để khớp với cấu trúc thư mục của bạn
    include_once(__DIR__ . '/../../config.php'); 

    $module = $module ?? ''; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8"> <meta name="viewport" content="width=device-width, initial-scale=1.0"> <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    
    <title><?php echo $page_title ?? 'Quản lý Khách sạn'; ?></title>
    <style> 
        /* Cung cấp một chút padding cho nội dung chính */
        .main-content {
            min-height: 100vh;
            padding-top: 20px; /* Thêm padding trên để nội dung không bị dính vào navbar */
        }

        /* Tùy chỉnh cho Navbar để làm nổi bật trang đang hoạt động */
        .navbar .nav-link.active {
            font-weight: bold;
            border-bottom: 2px solid white; 
            padding-bottom: 5px; 
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>admin/dashboard.php">
            <i class="fa fa-cogs"></i> Quản Trị Hệ Thống
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?php echo ($module == 'dashboard') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/dashboard.php"><i class="fa fa-tachometer-alt"></i> Thống kê</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($module == 'quan_ly_phong') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/manage_rooms.php"><i class="fa fa-list"></i> Quản lý Phòng</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($module == 'quan_ly_don') ? 'active' : ''; ?>" href="<?php echo BASE_URL; ?>admin/manage_bookings.php"><i class="fa fa-calendar-check"></i> Quản lý Đơn</a></li>
            </ul>
            <a href="<?php echo BASE_URL; ?>login/logout.php" class="btn btn-sm btn-danger d-flex align-items-center">
                <i class="fa fa-sign-out-alt me-1"></i> Đăng xuất
            </a>
        </div>
    </div>
</nav>
<div class="container-fluid main-content">