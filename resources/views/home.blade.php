@extends('layouts.app')

@section('title', 'Trang Chủ')

@section('content')
    <!-- Hero Banner -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 hero-content">
                    <h1 class="display-4 fw-bold mb-4">Hệ Thống Giáo Dục & Đào Tạo <span class="text-warning">Khai Trí</span></h1>
                    <p class="lead mb-4">Nâng cao tri thức - Phát triển tương lai với các chương trình đào tạo chất lượng cao, đội ngũ giảng viên giàu kinh nghiệm và phương pháp giảng dạy hiện đại.</p>
                    <div class="hero-buttons">
                        <a href="#courses" class="btn btn-light btn-lg">
                            <i class="fas fa-book me-2"></i>Khóa học
                        </a>
                        <a href="{{ route('news.index') }}" class="btn btn-outline-light btn-lg">
                            <i class="fas fa-newspaper me-2"></i>Tin tức
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="course-image rounded shadow-lg">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Courses -->
    <section id="courses" class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12">
                    <h2 class="fw-bold text-center mb-3">Khóa Học Nổi Bật</h2>
                    <p class="text-center text-muted lead">Các chương trình đào tạo chất lượng cao được thiết kế phù hợp với nhu cầu thực tế</p>
                </div>
            </div>
            <div class="row g-4">
                @php
                    $featuredCourses = \App\Models\Course::published()
                        ->with(['category', 'instructor'])
                        ->orderBy('created_at', 'desc')
                        ->limit(4)
                        ->get();
                @endphp
                
                @forelse($featuredCourses as $course)
                <div class="col-md-6 col-lg-3">
                    <div class="card course-card h-100">
                        <div class="course-image" style="background: linear-gradient(135deg, #2c5aa0 0%, #1e3a8a 100%); height: 180px;">
                            @if($course->thumbnail)
                                <img src="{{ $course->thumbnail_url }}" alt="{{ $course->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <i class="fas fa-book fa-2x"></i>
                            @endif
                            @if($course->is_featured)
                                <div class="course-badge">Nổi bật</div>
                            @endif
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge bg-primary">{{ $course->category->name ?? 'Khóa học' }}</span>
                                <small class="text-muted">{{ $course->level }}</small>
                            </div>
                            <h5 class="card-title fw-bold" style="font-size: 1.1rem;">{{ Str::limit($course->title, 50) }}</h5>
                            <p class="card-text text-muted small">{{ Str::limit($course->short_description, 80) }}</p>
                            
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="text-primary fw-bold h5 mb-0">{{ number_format($course->final_price) }}₫</span>
                                @if($course->sale_price && $course->sale_price < $course->price)
                                    <span class="text-muted text-decoration-line-through small">{{ number_format($course->price) }}₫</span>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-users me-1"></i>{{ $course->students_count }}
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-star text-warning me-1"></i>{{ number_format($course->rating, 1) }}
                                </small>
                            </div>

                            <div class="d-grid gap-2 mt-3">
                                @auth
                                    @php
                                        $isEnrolled = $course->isEnrolled();
                                        $isPending = \App\Models\CourseEnrollment::where('user_id', Auth::id())
                                            ->where('course_id', $course->id)
                                            ->where('status', 'pending')
                                            ->exists();
                                    @endphp
                                    
                                    @if($isEnrolled)
                                        <a href="{{ route('student.dashboard') }}" class="btn btn-success btn-sm">
                                            <i class="fas fa-check me-1"></i>Đã đăng ký
                                        </a>
                                    @elseif($isPending)
                                        <button class="btn btn-warning btn-sm" disabled>
                                            <i class="fas fa-clock me-1"></i>Chờ duyệt
                                        </button>
                                    @else
                                        <form action="{{ route('courses.enroll', $course) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                                <i class="fas fa-plus me-1"></i>Đăng ký ngay
                                            </button>
                                        </form>
                                    @endif
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-sign-in-alt me-1"></i>Đăng nhập để đăng ký
                                    </a>
                                @endauth
                                <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-info-circle me-1"></i>Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Đang cập nhật khóa học...</p>
                    @auth
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Thêm khóa học mới
                            </a>
                        @endif
                    @endauth
                </div>
                @endforelse
            </div>
            <div class="text-center mt-5">
                <a href="{{ route('courses.index') }}" class="btn btn-primary btn-lg">
                    <i class="fas fa-list me-2"></i>Xem tất cả khóa học
                </a>
            </div>
        </div>
    </section>

    <!-- Why Choose Us -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="fw-bold mb-3">Tại Sao Chọn Khai Trí?</h2>
                    <p class="text-muted lead">Cam kết mang đến chất lượng giáo dục tốt nhất với nhiều lợi ích vượt trội</p>
                </div>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-user-tie"></i>
                            </div>
                            <h5 class="fw-bold">Giảng Viên Chuyên Nghiệp</h5>
                            <p class="text-muted">Đội ngũ giảng viên giàu kinh nghiệm, nhiệt huyết với sự nghiệp giáo dục, luôn cập nhật phương pháp giảng dạy mới.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-laptop-house"></i>
                            </div>
                            <h5 class="fw-bold">Học Tập Linh Hoạt</h5>
                            <p class="text-muted">Đa dạng hình thức học tập: trực tiếp, online, hybrid. Thời gian linh hoạt phù hợp với mọi đối tượng học viên.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100">
                        <div class="card-body text-center p-4">
                            <div class="feature-icon">
                                <i class="fas fa-certificate"></i>
                            </div>
                            <h5 class="fw-bold">Chứng Chỉ Có Giá Trị</h5>
                            <p class="text-muted">Cấp chứng chỉ được công nhận toàn quốc, hỗ trợ giới thiệu việc làm và kết nối với các doanh nghiệp đối tác.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Latest News -->
    <section id="news" class="py-5">
        <div class="container">
            <div class="row mb-5">
                <div class="col-12">
                    <h2 class="fw-bold text-center mb-3">Tin Tức & Sự Kiện Mới Nhất</h2>
                    <p class="text-center text-muted lead">Cập nhật những thông tin mới nhất về giáo dục và đào tạo</p>
                </div>
            </div>
            <div class="row g-4">
                @php
                    // Lấy bài viết mới nhất, chỉ lấy published
                    $latestPosts = \App\Models\Post::published()
                        ->with(['author', 'category'])
                        ->orderBy('created_at', 'desc')
                        ->limit(3)
                        ->get();
                @endphp
                
                @forelse($latestPosts as $post)
                <div class="col-lg-4 col-md-6">
                    <div class="card course-card h-100">
                        <div class="course-image" style="background: linear-gradient(135deg, #ff6b35 0%, #e55a2b 100%); height: 200px;">
                            @if($post->featured_image)
                                <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                <i class="fas fa-newspaper fa-2x"></i>
                            @endif
                            @if($post->is_featured)
                                <div class="course-badge" style="background: #28a745;">Nổi bật</div>
                            @endif
                        </div>
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary">{{ $post->category->name ?? 'Tin tức' }}</span>
                                <small class="text-muted">
                                    @if($post->published_at)
                                        {{ $post->published_at->format('d/m/Y') }}
                                    @else
                                        {{ $post->created_at->format('d/m/Y') }}
                                    @endif
                                </small>
                            </div>
                            <h5 class="card-title fw-bold" style="font-size: 1.1rem;">{{ Str::limit($post->title, 60) }}</h5>
                            <p class="card-text text-muted">{{ Str::limit($post->excerpt ?? $post->content, 100) }}</p>
                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <small class="text-muted">
                                    <i class="fas fa-eye me-1"></i>{{ $post->view_count }} lượt xem
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-user me-1"></i>{{ $post->author->name ?? 'Admin' }}
                                </small>
                            </div>
                            <div class="d-grid mt-3">
                                <a href="{{ route('news.show', $post->slug) }}" class="btn btn-outline-primary btn-sm">
                                    Đọc tiếp <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-12 text-center py-5">
                    <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Chưa có tin tức nào được đăng.</p>
                    @auth
                        @if(Auth::user()->isAdmin())
                            <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Thêm tin tức mới
                            </a>
                        @endif
                    @endauth
                </div>
                @endforelse
            </div>
            
            @if($latestPosts->count() > 0)
            <div class="text-center mt-5">
                <a href="{{ route('news.index') }}" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-newspaper me-2"></i>Xem tất cả tin tức
                </a>
            </div>
            @endif
        </div>
    </section>

    <!-- Statistics -->
    <section class="py-5 bg-light">
        <div class="container">
            <div class="row text-center">
                <div class="col-6 col-md-3 mb-4">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <h2 class="fw-bold text-primary">5,000+</h2>
                    <p class="text-muted">Học viên</p>
                </div>
                <div class="col-6 col-md-3 mb-4">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-book"></i>
                    </div>
                    <h2 class="fw-bold text-primary">50+</h2>
                    <p class="text-muted">Khóa học</p>
                </div>
                <div class="col-6 col-md-3 mb-4">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h2 class="fw-bold text-primary">100+</h2>
                    <p class="text-muted">Giảng viên</p>
                </div>
                <div class="col-6 col-md-3 mb-4">
                    <div class="feature-icon mx-auto mb-3">
                        <i class="fas fa-award"></i>
                    </div>
                    <h2 class="fw-bold text-primary">98%</h2>
                    <p class="text-muted">Hài lòng</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card contact-card shadow">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4">Đăng Ký Tư Vấn Khóa Học</h2>
                            <p class="text-center text-muted mb-4">Để lại thông tin, chúng tôi sẽ liên hệ tư vấn miễn phí và hỗ trợ bạn chọn khóa học phù hợp</p>
                            <form id="contactForm">
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="name" required placeholder="Nhập họ và tên">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="phone" required placeholder="Nhập số điện thoại">
                                    </div>
                                    <div class="col-12">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" placeholder="Nhập email">
                                    </div>
                                    <div class="col-12">
                                        <label for="course" class="form-label">Khóa học quan tâm</label>
                                        <select class="form-select" id="course">
                                            <option selected>Chọn khóa học...</option>
                                            @foreach($featuredCourses as $course)
                                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="message" class="form-label">Nhu cầu học tập</label>
                                        <textarea class="form-control" id="message" rows="3" placeholder="Mô tả nhu cầu học tập của bạn..."></textarea>
                                    </div>
                                    <div class="col-12 text-center">
                                        <button type="submit" class="btn btn-submit btn-lg px-5">
                                            <i class="fas fa-paper-plane me-2"></i>Gửi thông tin
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection