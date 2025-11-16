<?php
// Tệp này chỉ làm nhiệm vụ hiển thị, không gọi config.php nữa.
// Các biến như $page_title và $module phải được định nghĩa TRƯỚC KHI include tệp này.

$module = $module ?? ''; 
// BASE_URL được giả định là đã có từ tệp View (my_bookings.php)
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Đặt phòng'; ?></title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    
    <style> 
        .main-content {
            min-height: 100vh;
        }
        .navbar .nav-link.active {
            font-weight: bold;
            border-bottom: 2px solid white;
            padding-bottom: 5px; 
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-success shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>user/rooms.php">
            <i class="fa fa-bed"></i> Đặt Phòng
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link <?php echo ($module == 'rooms') ? 'active' : ''; ?>" 
                   href="<?php echo BASE_URL; ?>user/rooms.php"><i class="fa fa-plus"></i> Đặt Phòng Mới</a></li>
                <li class="nav-item"><a class="nav-link <?php echo ($module == 'my_bookings') ? 'active' : ''; ?>" 
                   href="<?php echo BASE_URL; ?>user/my_bookings.php"><i class="fa fa-book"></i> Đơn Đặt Phòng Của Tôi</a></li>
            </ul>
            <a href="<?php echo BASE_URL; ?>login/logout.php" class="btn btn-sm btn-light d-flex align-items-center">
                <i class="fa fa-sign-out-alt me-1"></i> Đăng xuất
            </a>
        </div>
    </div>
</nav>
<div class="container-fluid main-content">