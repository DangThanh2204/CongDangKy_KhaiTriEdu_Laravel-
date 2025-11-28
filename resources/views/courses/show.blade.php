@extends('layouts.app')

@section('title', $course->title)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <img src="{{ $course->banner_image_url }}" class="card-img-top" alt="{{ $course->title }}" style="height: 400px; object-fit: cover;">
                
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-secondary mb-2">{{ $course->category->name ?? 'Chưa phân loại' }}</span>
                            <span class="badge bg-info ms-1">{{ $course->level }}</span>
                            @if($course->is_featured)
                                <span class="badge bg-warning ms-1">Nổi bật</span>
                            @endif
                            @if($course->is_popular)
                                <span class="badge bg-danger ms-1">Phổ biến</span>
                            @endif
                        </div>
                        <div class="text-end">
                            @if($course->sale_price)
                                <h2 class="text-primary fw-bold">{{ number_format($course->sale_price) }}₫</h2>
                                <small class="text-muted text-decoration-line-through">{{ number_format($course->price) }}₫</small>
                                <span class="badge bg-success ms-2">-{{ $course->discount_percentage }}%</span>
                            @else
                                <h2 class="text-primary fw-bold">{{ number_format($course->price) }}₫</h2>
                            @endif
                        </div>
                    </div>
                    
                    <h1 class="card-title fw-bold mb-3">{{ $course->title }}</h1>
                    <p class="card-text lead">{{ $course->description }}</p>
                    
                    <div class="row text-center mb-4">
                        <div class="col-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-clock fa-2x text-primary mb-2"></i>
                                <h5 class="fw-bold">{{ $course->duration }} giờ</h5>
                                <small class="text-muted">Thời lượng</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h5 class="fw-bold">{{ $course->students_count }}</h5>
                                <small class="text-muted">Học viên</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-star fa-2x text-primary mb-2"></i>
                                <h5 class="fw-bold">{{ $course->rating }}</h5>
                                <small class="text-muted">Đánh giá</small>
                            </div>
                        </div>
                        <div class="col-3">
                            <div class="border rounded p-3">
                                <i class="fas fa-book fa-2x text-primary mb-2"></i>
                                <h5 class="fw-bold">{{ $course->lessons_count }}</h5>
                                <small class="text-muted">Bài học</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <h4 class="fw-bold mb-3">Giảng viên</h4>
                        <div class="d-flex align-items-center">
                            <div class="avatar bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                                <i class="fas fa-user-tie text-white"></i>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1">{{ $course->instructor->fullname ?? $course->instructor->username }}</h5>
                                <p class="text-muted mb-0">Giảng viên</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 100px;">
                <div class="card-body text-center">
                    <h3 class="text-primary fw-bold mb-3">
                        @if($course->sale_price)
                            {{ number_format($course->sale_price) }}₫
                        @else
                            {{ number_format($course->price) }}₫
                        @endif
                    </h3>
                    
                    @if($isEnrolled)
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            Bạn đã đăng ký khóa học này
                        </div>
                        <a href="{{ route('student.dashboard') }}" class="btn btn-success w-100 mb-2">
                            <i class="fas fa-tachometer-alt me-2"></i>Vào học ngay
                        </a>
                        
                        <form action="{{ route('courses.unenroll', $course) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Bạn có chắc muốn hủy đăng ký khóa học này?')">
                                <i class="fas fa-times me-2"></i>Hủy đăng ký
                            </button>
                        </form>
                    @elseif($isPending)
                        <div class="alert alert-warning">
                            <i class="fas fa-clock me-2"></i>
                            Đang chờ phê duyệt
                        </div>
                        <button class="btn btn-warning w-100" disabled>
                            <i class="fas fa-hourglass-half me-2"></i>Chờ duyệt
                        </button>
                    @else
                        <form action="{{ route('courses.enroll', $course) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-3">
                                <i class="fas fa-plus me-2"></i>Đăng ký ngay
                            </button>
                        </form>
                        
                        <small class="text-muted">
                            <i class="fas fa-info-circle me-1"></i>
                            Yêu cầu đăng ký sẽ được duyệt trong vòng 24h
                        </small>
                    @endif
                    
                    <hr class="my-4">
                    
                    <div class="text-start">
                        <h6 class="fw-bold mb-3">Khóa học bao gồm:</h6>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-play-circle text-primary me-2"></i>
                            <span>{{ $course->lessons_count }} bài học video</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-file-alt text-primary me-2"></i>
                            <span>Tài liệu học tập đầy đủ</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-tasks text-primary me-2"></i>
                            <span>Bài tập thực hành</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-certificate text-primary me-2"></i>
                            <span>Chứng chỉ hoàn thành</span>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-infinity text-primary me-2"></i>
                            <span>Truy cập trọn đời</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Similar Courses -->
    @if($similarCourses->count() > 0)
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="fw-bold mb-4">Khóa học tương tự</h3>
                <div class="row g-4">
                    @foreach($similarCourses as $similarCourse)
                        <div class="col-md-6 col-lg-3">
                            <div class="card course-card h-100">
                                <div class="course-image position-relative">
                                    <img src="{{ $similarCourse->thumbnail_url }}" class="card-img-top" alt="{{ $similarCourse->title }}" style="height: 160px; object-fit: cover;">
                                </div>
                                
                                <div class="card-body">
                                    <h6 class="card-title fw-bold">{{ Str::limit($similarCourse->title, 50) }}</h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-primary fw-bold">
                                            @if($similarCourse->sale_price)
                                                {{ number_format($similarCourse->sale_price) }}₫
                                            @else
                                                {{ number_format($similarCourse->price) }}₫
                                            @endif
                                        </span>
                                        <a href="{{ route('courses.show', $similarCourse) }}" class="btn btn-sm btn-outline-primary">Chi tiết</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
@endsection