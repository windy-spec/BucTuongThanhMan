<?php
    // Luôn gọi config.php đầu tiên
    include_once('config.php');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống Quản lý Khách sạn - Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
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
            position: relative; /* Dùng để đặt icon quote */
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
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Trang chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo BASE_URL; ?>qlks/index.php">Dashboard QLKS</a>
                    </li>
                    
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-outline-primary btn-sm" href="<?php echo BASE_URL; ?>login/index.php">
                            <i class="fa fa-sign-in-alt me-1"></i> Đăng nhập
                        </a>
                    </li>
                    
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a class="btn btn-success btn-sm" href="<?php echo BASE_URL; ?>login/register.php">
                             <i class="fa fa-user-plus me-1"></i> Đăng ký
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="hero-section">
        <div class="container">
            <h1 class="display-3">Hệ thống Quản lý Khách sạn</h1>
            <p class="lead fs-4">Dự án học tập ứng dụng PHP & MySQL.</p>
        </div>
    </header>

    <section class="container my-5 py-5">
        <h2 class="section-title">Giới thiệu (Bịa)</h2>
        <div class="row align-items-center">
            <div class="col-lg-6">
                <img src="https://images.unsplash.com/photo-1564501049412-61c2a3083791?fit=crop&w=1200&q=80" 
                     class="img-fluid rounded shadow-lg" alt="Hotel Lobby">
            </div>
            <div class="col-lg-6 mt-4 mt-lg-0 ps-lg-5">
                <h3 class="fw-bold">Dự án "Khách sạn Tương Lai"</h3>
                <p class="lead text-muted">Một dự án mô phỏng nghiệp vụ quản lý khách sạn (UI) trong khuôn khổ môn học Lập trình Web PHP.</p>
                <p>Được "thành lập" (bịa) vào năm 2025, "Khách sạn Tương Lai" là một dự án mô phỏng được phát triển bởi các sinh viên tâm huyết, với mục tiêu ứng dụng công nghệ PHP 8+ và MySQL vào các nghiệp vụ thực tế. Hệ thống của chúng tôi (hiện tại là UI) bao gồm:</p>
                <ul class="list-unstyled text-muted">
                    <li><i class="fa fa-check text-success me-2"></i> Dashboard quản lý trực quan.</li>
                    <li><i class="fa fa-check text-success me-2"></i> Quản lý phòng (CRUD) đầy đủ.</li>
                    <li><i class="fa fa-check text-success me-2"></i> Giao diện sẵn sàng cho nghiệp vụ đặt phòng.</li>
                    <li><i class="fa fa-check text-success me-2"></i> Hệ thống quản lý bài tập (tự động) đi kèm.</li>
                </ul>
            </div>
        </div>
    </section>

    <section class="py-5 bg-white">
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

            </div> </div>
    </section>

    <section class="container my-5 py-5">
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

    <section class="py-5 bg-white">
        <div class="container my-5">
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
        </div>
    </section>

    <footer class="footer-custom pt-5 pb-4">
        <div class="container text-center">
            <p class="mb-2">Dự án học tập môn Lập trình Web PHP.</p>
            <p class="mb-0">© 2025 Phát triển bởi Bro.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>