@extends('layouts.app')

@section('title', 'Đối tác')

@section('content')
<section class="py-5 bg-primary text-white">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-4 fw-bold mb-3">Đối tác đồng hành cùng Khai Trí Education</h1>
                <p class="lead mb-0">Kết nối doanh nghiệp, tổ chức đào tạo và cộng đồng học viên để mở rộng cơ hội học tập và nghề nghiệp.</p>
            </div>
            <div class="col-lg-4 text-center">
                <i class="fas fa-handshake fa-5x opacity-50"></i>
            </div>
        </div>
    </div>
</section>

<section class="py-5">
    <div class="container">
        <div class="row mb-5">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="fw-bold text-primary mb-3">
                    <i class="fas fa-building me-2"></i>Mạng lưới hợp tác
                </h2>
                <p class="text-muted lead">
                    Khai Trí Education hợp tác cùng doanh nghiệp, trung tâm đào tạo và chuyên gia để xây dựng chương trình học bám sát thực tế.
                </p>
                <p class="text-muted mb-0">
                    Mỗi đối tác góp phần giúp học viên tiếp cận kiến thức mới, môi trường thực hành tốt hơn và cơ hội việc làm rõ ràng hơn sau khi hoàn thành khóa học.
                </p>
            </div>
            <div class="col-lg-6">
                <div class="partner-highlight h-100">
                    <div class="row g-3 text-center">
                        <div class="col-6"><div class="mini-stat"><h3 class="fw-bold text-primary mb-1">20+</h3><p class="mb-0 text-muted">Đơn vị đào tạo</p></div></div>
                        <div class="col-6"><div class="mini-stat"><h3 class="fw-bold text-primary mb-1">50+</h3><p class="mb-0 text-muted">Chuyên gia đồng hành</p></div></div>
                        <div class="col-6"><div class="mini-stat"><h3 class="fw-bold text-primary mb-1">100+</h3><p class="mb-0 text-muted">Cơ hội thực chiến</p></div></div>
                        <div class="col-6"><div class="mini-stat"><h3 class="fw-bold text-primary mb-1">5,000+</h3><p class="mb-0 text-muted">Học viên hưởng lợi</p></div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6 col-lg-3"><div class="card partner-card h-100 text-center"><div class="card-body p-4"><div class="partner-icon mb-3"><i class="fas fa-briefcase fa-2x text-primary"></i></div><h5 class="fw-bold">Doanh nghiệp</h5><p class="text-muted small mb-0">Đồng hành xây dựng kỹ năng thực tế và định hướng nghề nghiệp cho học viên.</p></div></div></div>
            <div class="col-md-6 col-lg-3"><div class="card partner-card h-100 text-center"><div class="card-body p-4"><div class="partner-icon mb-3"><i class="fas fa-school fa-2x text-primary"></i></div><h5 class="fw-bold">Trung tâm đào tạo</h5><p class="text-muted small mb-0">Cùng phát triển chương trình học và tài nguyên giảng dạy chất lượng.</p></div></div></div>
            <div class="col-md-6 col-lg-3"><div class="card partner-card h-100 text-center"><div class="card-body p-4"><div class="partner-icon mb-3"><i class="fas fa-user-tie fa-2x text-primary"></i></div><h5 class="fw-bold">Chuyên gia</h5><p class="text-muted small mb-0">Chia sẻ kinh nghiệm, cố vấn lộ trình học tập và hỗ trợ thực hành.</p></div></div></div>
            <div class="col-md-6 col-lg-3"><div class="card partner-card h-100 text-center"><div class="card-body p-4"><div class="partner-icon mb-3"><i class="fas fa-certificate fa-2x text-primary"></i></div><h5 class="fw-bold">Đơn vị chứng nhận</h5><p class="text-muted small mb-0">Nâng cao giá trị đầu ra cho học viên sau khi hoàn thành khóa học.</p></div></div></div>
        </div>

        <div class="row align-items-center">
            <div class="col-lg-7 mb-4 mb-lg-0">
                <h2 class="fw-bold text-primary mb-3"><i class="fas fa-link me-2"></i>Muốn trở thành đối tác?</h2>
                <p class="text-muted mb-3">Nếu đơn vị của bạn muốn hợp tác cùng Khai Trí Education trong đào tạo, tuyển dụng hoặc tổ chức chương trình học thực tế, chúng tôi luôn sẵn sàng trao đổi.</p>
                <p class="text-muted mb-0">Hãy liên hệ để cùng xây dựng các hoạt động mang lại giá trị thực cho học viên và cộng đồng.</p>
            </div>
            <div class="col-lg-5">
                <div class="partner-cta">
                    <h5 class="fw-bold mb-3">Kết nối với chúng tôi</h5>
                    <p class="text-muted mb-4">Đội ngũ Khai Trí sẽ phản hồi và trao đổi chi tiết về mô hình hợp tác phù hợp.</p>
                    <a href="{{ route('contact') }}" class="btn btn-primary btn-lg w-100 mb-3"><i class="fas fa-envelope me-2"></i>Liên hệ hợp tác</a>
                    <a href="{{ route('about') }}" class="btn btn-outline-primary btn-lg w-100"><i class="fas fa-info-circle me-2"></i>Tìm hiểu về Khai Trí</a>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.partner-highlight,
.partner-cta,
.partner-card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.partner-highlight,
.partner-cta {
    padding: 30px;
    background: #fff;
}

.partner-card {
    transition: all 0.3s ease;
}

.partner-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.14);
}

.partner-icon {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    background: rgba(44, 90, 160, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}

.mini-stat {
    padding: 18px 14px;
    border-radius: 10px;
    background: #f8f9fa;
}
</style>
@endsection
