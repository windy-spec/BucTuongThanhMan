<?php
    // Luôn gọi config.php đầu tiên
    include_once('config.php');

    // Logic kiểm tra đăng nhập (Giả định biến $_SESSION['user_id'] hoặc tương tự tồn tại khi đăng nhập)
    // Tạm thời khởi động session để kiểm tra trạng thái
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    $is_logged_in = isset($_SESSION['user_id']) && $_SESSION['user_id'];
    
    // Lấy BASE_URL cho việc chuyển hướng
    $base_url_js = BASE_URL; 
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Khách sạn - Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Navbar */
        .navbar-brand { font-weight: 600; }
        
        /* Hero Section (Theme: Hotel) */
        .hero-section {
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?fit=crop&w=1920&q=80');
            height: 70vh;
            background-position: center;
            background-size: cover;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .hero-section h1 {
            font-weight: 700;
            text-shadow: 2px 2px 8px rgba(0,0,0,0.7);
        }

        /* Tiêu đề chung cho các section */
        .section-title {
            text-align: center;
            margin-bottom: 4rem;
            font-weight: 700;
            color: #343a40;
            position: relative;
        }
        .section-title::after {
            content: '';
            width: 70px;
            height: 4px;
            background-color: #0d6efd;
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
        }

        /* Card Module */
        .card-module { text-decoration: none; color: inherit; }
        .card-module .card { 
            transition: transform 0.2s, box-shadow 0.2s; 
            border: none;
            border-radius: 0.75rem;
            height: 100%;
        }
        .card-module .card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
        }
        .card-module .card-icon { font-size: 3rem; margin-bottom: 1rem; }
        .card-module.main-module .card {
            background-color: #0d6efd;
            color: white;
        }
        .card-module.main-module .card-icon { color: white; }
        .card-module.main-module .card-text { color: rgba(255,255,255,0.8); }
        
        /* Đánh giá (Testimonials) */
        .testimonial-card {
            background-color: #fff;
            border-radius: 0.75rem;
        }
        .testimonial-card .card-body {
            position: relative; 
        }
        .testimonial-card .quote-icon {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 2.5rem;
            color: #e9ecef;
            z-index: 1;
        }
        .testimonial-card blockquote {
            position: relative;
            z-index: 2;
        }

        /* Footer */
        .footer-custom {
            background-color: #212529;
            color: #adb5bd;
            padding: 40px 0;
            margin-top: 5rem;
        }

        /* Hiệu ứng fade-up */
        .fade-up {
            opacity: 0;
            transform: translateY(40px);
            transition: all 1.4s ease;
        }

        /* Khi xuất hiện trong viewport */
        .fade-up.show {
            opacity: 1;
            transform: translateY(0);
        }
        /* Carousel and side previews: increased height + larger preview thumbnails */
        .carousel-inner .carousel-item { height: 590px; min-height:420px; background-size: cover; background-position: center; }
        .side-preview { width:140px; }
        .side-preview img { height:140px; width:140px; object-fit:cover; border-radius:10px; box-shadow: 0 12px 30px rgba(0,0,0,0.14); }
        .side-preview-right { right:-52px; top:50%; transform:translateY(-50%); }
        .side-preview-left { left:-52px; top:50%; transform:translateY(-50%); }
        @media (max-width:992px) {
            .carousel-inner .carousel-item { height:320px; min-height:320px; }
            .side-preview { display:none; }
        }
        /* Tùy chỉnh nền card và bóng đổ (nếu cần) */
        .custom-search-card {
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
            border-radius: 12px;
        }

        /* Đảm bảo label hiển thị rõ ràng */
        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #495057; 
        }

        /* Đảm bảo nút bấm được căn chỉnh đều với các input khác */
        .align-self-end {
            padding-top: 0; 
        }

        /* Tùy chỉnh nút bấm để nổi bật hơn */
        .custom-btn {
            padding: 0.75rem 0.5rem; 
            font-size: 1rem;
            font-weight: bold;
            border-radius: 8px; 
            transition: background-color 0.3s ease;
            height: 100%; 
        }

        .custom-btn:hover {
            background-color: #0056b3; 
        }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm">
        <div class="container">
            <a class="navbar-brand text-primary" href="#">
                <i class="fa fa-graduation-cap"></i>
                Project: Hotel Management
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center">
                    <li class="nav-item"><a class="nav-link" href="#">Trang chủ</a></li>
                    <li class="nav-item"><a class="nav-link" href="user/rooms.php">Phòng</a></li>
                    <li class="nav-item"><a class="nav-link" href="user/my_bookings.php">Đơn của tôi</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Liên hệ</a></li>
                    <?php if ($is_logged_in): ?>
    <li class="nav-item dropdown ms-3">
        <a class="btn btn-outline-primary btn-sm dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-user-circle me-1"></i> Tài khoản
        </a>
        <ul class="dropdown-menu dropdown-menu-end">
            <li><a class="dropdown-item" href="user/my_bookings.php"><i class="fa fa-list-alt me-2"></i>Đơn của tôi</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item text-danger" href="<?php echo BASE_URL; ?>login/logout.php"><i class="fa fa-sign-out-alt me-2"></i>Đăng xuất</a></li>
        </ul>
    </li>
<?php else: ?>
    <li class="nav-item ms-3">
        <a class="btn btn-outline-primary btn-sm" href="<?php echo BASE_URL; ?>login/index.php">
            <i class="fa fa-sign-in-alt me-1"></i> Đăng nhập
        </a>
    </li>
    <li class="nav-item ms-2">
        <a class="btn btn-primary btn-sm" href="<?php echo BASE_URL; ?>login/register.php">
                <i class="fa fa-user-plus me-1"></i> Đăng ký
        </a>
    </li>
<?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="container">
            <h1 class="display-3">Chào mừng đến với Khách sạn Tương Lai</h1>
            <p class="lead fs-5">Trải nghiệm đặt phòng nhanh chóng, giao diện trực quan, mô phỏng nghiệp vụ quản lý khách sạn.</p>

            <div class="row justify-content-center mt-4">
                <div class="col-lg-8">
                    <div class="card p-4 custom-search-card" style="background:rgba(255,255,255,0.95);"> 
                        <form class="row g-3 align-items-center" onsubmit="return false;"> 
                            <div class="col-md-3">
                                <label for="checkInDate" class="form-label mb-1">Ngày nhận</label>
                                <input type="text" class="form-control" id="checkInDate" placeholder="dd/mm/yyyy">
                            </div>
                            <div class="col-md-3">
                                <label for="checkOutDate" class="form-label mb-1">Ngày trả</label>
                                <input type="text" class="form-control" id="checkOutDate" placeholder="dd/mm/yyyy">
                            </div>
                            <div class="col-md-4">
                                <label for="estimatedPrice" class="form-label mb-1">Tổng tiền dự tính</label>
                                <input type="text" class="form-control text-left" id="estimatedPrice" placeholder="Mức dự tính" inputmode="numeric">
                            </div>
                            <div class="col-md-2 d-grid align-self-end">
                                <button type="button" class="btn btn-primary custom-btn" onclick="searchRoomsByMaxPrice()">Tìm phòng</button>
                            </div>
                            <div class="col-12 mt-3">
                                <p id="resultDisplay" class="text-info"></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <section class="container my-5 py-5 fade-up">
        <h2 class="section-title">Giới thiệu</h2>
        <div class="row align-items-center gy-4">
            <div class="col-lg-6">
                <div class="p-4 rounded shadow-sm" style="background:linear-gradient(180deg,#ffffff,#fbfdff)">
                    <h3 class="fw-bold">Khách sạn Tương Lai</h3>
                    <p class="text-muted">Đến với Khách sạn Tương Lai, bạn sẽ được hòa mình vào thiên nhiên trong lành, tham gia các hoạt động vui chơi giải trí, thư giãn và nghỉ ngơi để thoát khỏi mọi muộn phiền.</p>
                    <p class="text-muted">Với không gian nghỉ dưỡng mang đậm nét thiên nhiên, hòa quyện tinh tế cùng nội thất tiêu chuẩn và dịch vụ hoàn hảo, đây sẽ là điểm dừng chân lý tưởng để du khách thư giãn và khơi dậy mọi giác quan.</p>
                    <ul class="list-unstyled text-muted mt-3">
                        <li class="mb-2"><i class="fa fa-check text-success me-2"></i> Không gian xanh, gần gũi thiên nhiên.</li>
                        <li class="mb-2"><i class="fa fa-check text-success me-2"></i> Phòng tiện nghi chuẩn 4-5 sao.</li>
                        <li class="mb-2"><i class="fa fa-check text-success me-2"></i> Dịch vụ chu đáo, nhanh chóng.</li>
                    </ul>
                    <div class="mt-4">
                        <a href="user/rooms.php" class="btn btn-primary me-2">Xem phòng</a>
                        <a href="#" class="btn btn-outline-secondary">Tìm hiểu thêm</a>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="position-relative">
                    <?php
                        // Prefer local assets when present
                        $img1 = file_exists(__DIR__ . '/assets/img/intro1.jpg') ? 'assets/img/intro1.jpg' : 'https://images.unsplash.com/photo-1505691723518-36a2f1a27b22?fit=crop&w=1200&q=80';
                        $img2 = file_exists(__DIR__ . '/assets/img/intro2.jpg') ? 'assets/img/intro2.jpg' : 'https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?fit=crop&w=1200&q=80';
                        $img3 = file_exists(__DIR__ . '/assets/img/intro3.jpg') ? 'assets/img/intro3.jpg' : 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?fit=crop&w=1200&q=80';
                        $imgs = [$img1, $img2, $img3];
                    ?>

                    <div id="introCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="2000" data-bs-pause="false">
                        <div class="carousel-inner rounded shadow-sm overflow-hidden">
                            <?php foreach ($imgs as $i => $src): ?>
                                <div class="carousel-item<?php echo $i === 0 ? ' active' : ''; ?>" style="background-image:url('<?php echo $src; ?>');"></div>
                            <?php endforeach; ?>
                        </div>
                        <button class="carousel-control-prev" type="button" data-bs-target="#introCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#introCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </div>

                    <div class="d-none d-md-block position-absolute side-preview side-preview-right">
                        <img src="<?php echo $img2; ?>" alt="preview" class="img-fluid rounded shadow">
                    </div>
                    <div class="d-none d-md-block position-absolute side-preview side-preview-left">
                        <img src="<?php echo $img3; ?>" alt="preview" class="img-fluid rounded shadow">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white fade-up">
        <div class="container my-5">
            <h2 class="section-title">Trung tâm Điều hành</h2>
            <div class="row g-4 justify-content-center">
                <div class="col-lg-6 col-md-12">
                    <div class="row g-4">
                        <div class="col-lg-6 col-md-6 card-module">
                            <a href="<?php echo BASE_URL; ?>bai_tap/bt_list.php?user=bt_khoa" class="card-link">
                                <div class="card p-3 text-center shadow-sm">
                                    <div class="card-icon text-success"><i class="fa fa-book"></i></div>
                                    <h5 class="card-title fs-5 fw-semibold">Bài tập Khoa</h5>
                                    <p class="card-text text-muted small">Xem bài tập (tự động).</p>
                                </div>
                            </a>
                        </div>
                        <div class="col-lg-6 col-md-6 card-module">
                            <a href="<?php echo BASE_URL; ?>bai_tap/bt_list.php?user=bt_phong" class="card-link">
                                <div class="card p-3 text-center shadow-sm">
                                    <div class="card-icon text-info"><i class="fa fa-user-graduate"></i></div>
                                    <h5 class="card-title fs-5 fw-semibold">Bài tập Phong</h5>
                                    <p class="card-text text-muted small">Xem bài tập (tự động).</p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </section>

    <section class="container my-5 py-5 fade-up">
        <h2 class="section-title">Phòng nổi bật</h2>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <a class="card-module" href="javascript:void(0)" onclick="checkLoginAndRedirect('user/rooms.php')">
                    <div class="card p-0 shadow-sm h-100">
                        <img src="https://images.unsplash.com/photo-1505691723518-36a2f1a27b22?fit=crop&w=1200&q=80" class="card-img-top" style="height:180px;object-fit:cover;" alt="">
                        <div class="card-body">
                            <h5 class="card-title">Deluxe Room</h5>
                            <p class="card-text text-muted small">Không gian tiện nghi, ban công hướng phố.</p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="fw-bold text-danger">1.200.000 VNĐ / đêm</div>
                                <button class="btn btn-sm btn-primary">Xem & Đặt</button>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a class="card-module" href="javascript:void(0)" onclick="checkLoginAndRedirect('user/rooms.php')">
                    <div class="card p-0 shadow-sm h-100">
                        <img src="https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?fit=crop&w=1200&q=80" class="card-img-top" style="height:180px;object-fit:cover;" alt="">
                        <div class="card-body">
                            <h5 class="card-title">Suite</h5>
                            <p class="card-text text-muted small">Phòng rộng, thích hợp gia đình hoặc công tác.</p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="fw-bold text-danger">2.800.000 VNĐ / đêm</div>
                                <button class="btn btn-sm btn-primary">Xem & Đặt</button>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
            <div class="col-md-6 col-lg-4">
                <a class="card-module" href="javascript:void(0)" onclick="checkLoginAndRedirect('user/rooms.php')">
                    <div class="card p-0 shadow-sm h-100">
                        <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?fit=crop&w=1200&q=80" class="card-img-top" style="height:180px;object-fit:cover;" alt="">
                        <div class="card-body">
                            <h5 class="card-title">Standard</h5>
                            <p class="card-text text-muted small">Lựa chọn tiết kiệm, sạch sẽ và tiện lợi.</p>
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <div class="fw-bold text-danger">850.000 VNĐ / đêm</div>
                                <button class="btn btn-sm btn-primary">Xem & Đặt</button>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>
    
    <section class="container my-5 py-5 fade-up">
        <h2 class="section-title">Đánh giá (Bịa)</h2>
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm h-100 testimonial-card">
                    <div class="card-body p-4">
                        <div class="quote-icon"><i class="fa fa-quote-right"></i></div>
                        <div class="d-flex mb-3">
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                        </div>
                        <blockquote class="blockquote mb-0">
                            <p>"Giao diện UI/UX rất sạch sẽ và chuyên nghiệp. Cấu trúc module rõ ràng, sẵn sàng để tích hợp backend."</p>
                            <footer class="blockquote-footer mt-3">Giảng viên A (Bịa), <cite>Khoa CNTT</cite></footer>
                        </blockquote>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm h-100 testimonial-card">
                    <div class="card-body p-4">
                        <div class="quote-icon"><i class="fa fa-quote-right"></i></div>
                        <div class="d-flex mb-3">
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star-half-alt text-warning"></i>
                        </div>
                        <blockquote class="blockquote mb-0">
                            <p>"Phần dashboard QLKS rất trực quan, đặc biệt là sơ đồ trạng thái phòng. Rất hứa hẹn cho một dự án thực tế."</p>
                            <footer class="blockquote-footer mt-3">Khách B (Bịa), <cite>Giả lập</cite></footer>
                        </blockquote>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow-sm h-100 testimonial-card">
                    <div class="card-body p-4">
                        <div class="quote-icon"><i class="fa fa-quote-right"></i></div>
                        <div class="d-flex mb-3">
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                            <i class="fa fa-star text-warning"></i>
                        </div>
                        <blockquote class="blockquote mb-0">
                            <p>"Module bài tập tự động giải quyết được vấn đề 403 của host free. Rất thông minh!"</p>
                            <footer class="blockquote-footer mt-3">Sinh viên C (Bịa), <cite>Lớp PHP</cite></footer>
                        </blockquote>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="container my-5 py-5 fade-up">
        <h2 class="section-title">Địa chỉ (Giả lập)</h2>
        <div class="row g-4">
            <div class="col-lg-6">
                <h3 class="fw-bold">Văn phòng Dự án (Bịa)</h3>
                <p>Đây là dự án học tập, toàn bộ thông tin dưới đây là giả lập để làm đầy trang web.</p>
                <ul class="list-unstyled fs-5" style="line-height: 2;">
                    <li><i class="fa fa-map-marker-alt text-primary me-2"></i> 123 Đường ABC, Phường XYZ, Quận 1, TP.HCM</li>
                    <li><i class="fa fa-phone text-primary me-2"></i> (028) 38 123 456</li>
                    <li><i class="fa fa-envelope text-primary me-2"></i> info@project-hotel.edu.vn</li>
                </ul>
            </div>
            <div class="col-lg-6">
                <div class="ratio ratio-16x9 shadow rounded">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.447411304922!2d106.6294103148007!3d10.77699119232076!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x317529d1800c63f9%3A0x1cba1bca10b0e004!2zQsOQIFPDtG5nIFZp4buHdCBIdXNzdG9u!5e0!3m2!1sen!2s!4v1678888888888" 
                            width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer-custom pt-5 pb-4">
        <div class="container text-center">
            <p class="mb-2">Dự án học tập môn Lập trình Web PHP.</p>
            <p class="mb-0">© 2025 Phát triển bởi Bro.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/vn.js"></script>

    <script>
    // --- KHAI BÁO BIẾN PHP SANG JS ---
    const IS_LOGGED_IN = <?php echo json_encode($is_logged_in); ?>;
    const BASE_URL_JS = <?php echo json_encode($base_url_js); ?>;

    let checkInPicker;
    let checkOutPicker;

    // --- HÀM KIỂM TRA ĐĂNG NHẬP (Dùng cho các nút Xem & Đặt/Tìm phòng) ---
    function checkLoginAndRedirect(targetUrl = null) {
        if (IS_LOGGED_IN) {
            if (targetUrl) {
                window.location.href = targetUrl;
            }
        } else {
            Swal.fire({
                title: 'Vui lòng Đăng nhập',
                text: "Bạn cần đăng nhập để tiến hành đặt phòng.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Đăng nhập ngay',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#0d6efd'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = BASE_URL_JS + 'login/index.php';
                }
            });
        }
    }

    // --- HÀM FORMAT TIỀN TỆ (từ 1200000 thành 1.200.000) ---
    function formatCurrency(input) {
        let value = input.value.replace(/\./g, ''); 
        
        if (isNaN(value) || value === '') {
            input.value = '';
            return;
        }

        const formatted = new Intl.NumberFormat('vi-VN').format(value);
        input.value = formatted;
    }
    
    // --- HÀM TÌM KIẾM CHÍNH (ĐÃ FIX LỖI HIỂN THỊ KÌ KÌ) ---
    function searchRoomsByMaxPrice() {
        if (!IS_LOGGED_IN) {
            checkLoginAndRedirect(); 
            return;
        }
        
        const resultDisplay = document.getElementById('resultDisplay');
        
        // 1. Lấy giá trị và kiểm tra (sử dụng selectedDates để đảm bảo logic ngày)
        const date1 = checkInPicker.selectedDates[0];
        const date2 = checkOutPicker.selectedDates[0];
        
        const totalPriceDisplay = document.getElementById('estimatedPrice').value;
        const totalPriceClean = parseFloat(totalPriceDisplay.replace(/\./g, ''));
        
        if (!date1 || !date2 || isNaN(totalPriceClean) || totalPriceClean <= 0) {
            resultDisplay.innerHTML = '<span class="text-danger">Vui lòng nhập đầy đủ Ngày nhận, Ngày trả và Tổng kinh phí dự tính hợp lệ.</span>';
            return;
        }

        // Tính số đêm
        const timeDiff = date2.getTime() - date1.getTime();
        const dayDiff = Math.ceil(timeDiff / (1000 * 3600 * 24)); 
        
        if (dayDiff <= 0) {
            resultDisplay.innerHTML = '<span class="text-danger">Ngày trả phải sau Ngày nhận ít nhất 1 đêm.</span>';
            return;
        }

        // 2. TÍNH TOÁN VÀ ĐỊNH DẠNG:
        // Dùng Math.floor để đảm bảo giá tối đa/đêm là số nguyên
        const estimatedPricePerNight = Math.floor(totalPriceClean / dayDiff);

        // Định dạng giá/đêm để hiển thị
        const formattedPricePerNight = estimatedPricePerNight.toLocaleString('vi-VN', { 
            style: 'currency', 
            currency: 'VND',
            minimumFractionDigits: 0
        }).replace('₫', ' VNĐ'); 
        
        // Định dạng ngày cho hiển thị (DD/MM/YYYY)
        const checkInDisplay = date1.toLocaleDateString('vi-VN');
        const checkOutDisplay = date2.toLocaleDateString('vi-VN');

        // 3. Cập nhật hiển thị kết quả gọn gàng (KHÔNG CHUYỂN HƯỚNG TỚI ROOMS.PHP)
        resultDisplay.innerHTML = `
            <span class="text-white">Phòng trống từ ${checkInDisplay} đến ${checkOutDisplay} với giá &le; ${formattedPricePerNight}/đêm.</span>
        `;
        
        // 4. CHUYỂN HƯỚNG TÌM KIẾM
        const checkInURL = checkInPicker.input.value; // YYYY-MM-DD
        const checkOutURL = checkOutPicker.input.value; // YYYY-MM-DD

        // Sau khi hiển thị thông báo, mới chuyển hướng để thực hiện lọc phòng
        window.location.href = `user/rooms.php?check_in=${checkInURL}&check_out=${checkOutURL}&max_price=${estimatedPricePerNight}`;
    }

    // --- KHỞI TẠO FLATPICKR VÀ GẮN EVENT ---
    document.addEventListener("DOMContentLoaded", () => {
        // 1. Cấu hình Flatpickr
        checkInPicker = flatpickr("#checkInDate", {
            locale: "vn",
            dateFormat: "Y-m-d", // Format dữ liệu gửi đi (YYYY-MM-DD)
            altInput: true,
            altFormat: "d/m/Y", // Format hiển thị cho người dùng
            minDate: "today", 
            onChange: function(selectedDates, dateStr, instance) {
                if (selectedDates.length > 0) {
                    checkOutPicker.set('minDate', selectedDates[0]);
                    const checkOutVal = checkOutPicker.selectedDates[0];
                    if (checkOutVal && checkOutVal.getTime() <= selectedDates[0].getTime()) {
                        checkOutPicker.clear();
                    }
                }
            }
        });

        checkOutPicker = flatpickr("#checkOutDate", {
            locale: "vn",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            minDate: "today" 
        });

        // 2. Gắn sự kiện định dạng tiền tệ cho input
        const priceInput = document.getElementById('estimatedPrice');
        priceInput.addEventListener('keyup', function() {
            formatCurrency(this);
        });

        // 3. Hiệu ứng fade-up
        const elements = document.querySelectorAll('.fade-up');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('show');
                }
            });
        }, { threshold: 0.15 });
        elements.forEach(el => observer.observe(el));
    });
    </script>
</body>
</html>