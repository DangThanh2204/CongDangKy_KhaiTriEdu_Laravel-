@extends('layouts.app')

@section('title', 'Giới Thiệu')

@section('content')
<!-- Banner Section -->
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Về Khai Trí Education</h1>
                <p class="lead mb-0">Nền tảng giáo dục trực tuyến hàng đầu tại Việt Nam, cam kết mang đến chất lượng giáo dục tốt nhất</p>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-graduation-cap fa-5x opacity-50"></i>
            </div>
        </div>
    </div>
</section>

<!-- About Content -->
<section class="py-5">
    <div class="container">
        <!-- Tầm Nhìn & Nhiệm Vụ -->
        <div class="row mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="fw-bold mb-3 text-primary">
                    <i class="fas fa-lightbulb me-2"></i>Tầm Nhìn
                </h2>
                <p class="text-muted lead">
                    Trở thành nền tảng giáo dục trực tuyến được tin cậy nhất tại Việt Nam, mang lại cơ hội học tập bình đẳng cho mọi người, bất kể họ ở đâu.
                </p>
                <p class="text-muted">
                    Chúng tôi tin rằng giáo dục chất lượng là chìa khóa để mở ra những cơ hội mới và phát triển bản thân.
                </p>
            </div>
            <div class="col-lg-6">
                <h2 class="fw-bold mb-3 text-primary">
                    <i class="fas fa-tasks me-2"></i>Nhiệm Vụ
                </h2>
                <p class="text-muted lead">
                    Cung cấp các khóa học chất lượng cao, với nội dung hiện đại và giảng viên chuyên nghiệp, giúp học viên phát triển kỹ năng cần thiết để thành công trong sự nghiệp.
                </p>
                <p class="text-muted">
                    Hỗ trợ học viên từ giai đoạn khám phá cho đến khi hoàn thành khóa học và đạt được mục tiêu của mình.
                </p>
            </div>
        </div>

        <hr class="my-5">

        <!-- Giá Trị Cốt Lõi -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="fw-bold text-center mb-5 text-primary">
                    <i class="fas fa-star me-2"></i>Giá Trị Cốt Lõi
                </h2>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card value-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="value-icon mb-3">
                            <i class="fas fa-certificate fa-2x text-primary"></i>
                        </div>
                        <h5 class="fw-bold">Chất Lượng</h5>
                        <p class="text-muted small">Cam kết mang đến nội dung và giảng viên tốt nhất</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card value-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="value-icon mb-3">
                            <i class="fas fa-lightbulb fa-2x text-warning"></i>
                        </div>
                        <h5 class="fw-bold">Sáng Tạo</h5>
                        <p class="text-muted small">Liên tục cập nhật phương pháp và công nghệ dạy học mới</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card value-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="value-icon mb-3">
                            <i class="fas fa-handshake fa-2x text-success"></i>
                        </div>
                        <h5 class="fw-bold">Tin Tưởng</h5>
                        <p class="text-muted small">Lấy sự hài lòng của học viên làm tiêu chí đánh giá</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 mb-4">
                <div class="card value-card h-100">
                    <div class="card-body text-center p-4">
                        <div class="value-icon mb-3">
                            <i class="fas fa-shield-alt fa-2x text-info"></i>
                        </div>
                        <h5 class="fw-bold">Hỗ Trợ</h5>
                        <p class="text-muted small">Luôn sẵn sàng giúp đỡ học viên đạt mục tiêu</p>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-5">

        <!-- Lịch Sử -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="fw-bold text-center mb-5 text-primary">
                    <i class="fas fa-history me-2"></i>Hành Trình Phát Triển
                </h2>
            </div>
            <div class="col-12">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker">2020</div>
                        <div class="timeline-content">
                            <h5 class="fw-bold">Thành Lập Khai Trí</h5>
                            <p class="text-muted">Khai Trí Education được thành lập với mục tiêu cung cấp giáo dục chất lượng cao trên nền tảng trực tuyến.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker">2021</div>
                        <div class="timeline-content">
                            <h5 class="fw-bold">Phát Triển Nhanh Chóng</h5>
                            <p class="text-muted">Tăng lên 1000+ học viên và 20+ khóa học, mở rộng các lĩnh vực giáo dục.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker">2022</div>
                        <div class="timeline-content">
                            <h5 class="fw-bold">Đạt Cột Mốc 5000+ Học Viên</h5>
                            <p class="text-muted">Vượt ngưỡng 5000 học viên, 50+ khóa học, và 100+ giảng viên chuyên nghiệp.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker">2023</div>
                        <div class="timeline-content">
                            <h5 class="fw-bold">Mở Rộng Quốc Tế</h5>
                            <p class="text-muted">Bắt đầu hợp tác với các tổ chức giáo dục quốc tế, mở rộng sang các thị trường mới.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-marker">2024-Hiện Tại</div>
                        <div class="timeline-content">
                            <h5 class="fw-bold">Tiếp Tục Phát Triển</h5>
                            <p class="text-muted">Liên tục cập nhật công nghệ, mở rộng khóa học và tăng cường chất lượng dịch vụ.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-5">

        <!-- Đội Ngũ -->
        <div class="row">
            <div class="col-12">
                <h2 class="fw-bold text-center mb-5 text-primary">
                    <i class="fas fa-users me-2"></i>Đội Ngũ Chúng Tôi
                </h2>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card team-card h-100 text-center">
                    <div class="card-body p-4">
                        <div class="team-icon mb-3">
                            <i class="fas fa-user-tie fa-3x text-primary"></i>
                        </div>
                        <h5 class="fw-bold">100+ Giảng Viên</h5>
                        <p class="text-muted small">Đội ngũ giảng viên chuyên nghiệp, giàu kinh nghiệm từ các lĩnh vực khác nhau</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card team-card h-100 text-center">
                    <div class="card-body p-4">
                        <div class="team-icon mb-3">
                            <i class="fas fa-user-graduated fa-3x text-primary"></i>
                        </div>
                        <h5 class="fw-bold">5000+ Học Viên</h5>
                        <p class="text-muted small">Cộng đồng học viên đa dạng, từ sinh viên đến những người đi làm</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4 mb-4">
                <div class="card team-card h-100 text-center">
                    <div class="card-body p-4">
                        <div class="team-icon mb-3">
                            <i class="fas fa-headset fa-3x text-primary"></i>
                        </div>
                        <h5 class="fw-bold">Hỗ Trợ 24/7</h5>
                        <p class="text-muted small">Đội ngũ hỗ trợ khách hàng luôn sẵn sàng giải đáp thắc mắc</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">Sẵn Sàng Bắt Đầu Hành Trình Học Tập?</h2>
        <p class="text-muted mb-4 lead">Tham gia cùng hàng nghìn học viên khác và phát triển kỹ năng của bạn</p>
        <a href="{{ route('courses.index') }}" class="btn btn-primary btn-lg me-3">
            <i class="fas fa-book me-2"></i>Khám Phá Khóa Học
        </a>
        <a href="{{ route('contact') }}" class="btn btn-outline-primary btn-lg">
            <i class="fas fa-envelope me-2"></i>Liên Hệ Với Chúng Tôi
        </a>
    </div>
</section>

<style>
.value-card {
    border: none;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.value-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.value-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(44, 90, 160, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 100%;
    background: linear-gradient(180deg, #2c5aa0 0%, #ff6b35 100%);
}

.timeline-item {
    margin-bottom: 30px;
    width: 45%;
}

.timeline-item:nth-child(odd) {
    margin-left: 0;
    text-align: right;
    padding-right: 50px;
}

.timeline-item:nth-child(even) {
    margin-left: 55%;
    padding-left: 50px;
}

.timeline-marker {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: white;
    border: 4px solid #2c5aa0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: #2c5aa0;
    margin-top: 0;
}

.timeline-item:nth-child(1) .timeline-marker {
    background: linear-gradient(135deg, #2c5aa0 0%, #1e3a8a 100%);
    color: white;
}

.timeline-item:nth-child(2) .timeline-marker {
    background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%);
    color: white;
}

.timeline-content {
    padding: 15px 20px;
    background: #f8f9fa;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.timeline-content:hover {
    background: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.team-card {
    border: none;
    border-radius: 12px;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.team-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.15);
}

.team-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(44, 90, 160, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

@media (max-width: 768px) {
    .timeline::before {
        left: 15px;
    }

    .timeline-item,
    .timeline-item:nth-child(odd),
    .timeline-item:nth-child(even) {
        width: 100%;
        margin-left: 0;
        padding-left: 50px;
        text-align: left;
        padding-right: 0;
    }

    .timeline-marker {
        left: 0;
        transform: none;
    }
}
</style>
@endsection
