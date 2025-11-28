@extends('layouts.app')

@section('title', 'Danh Sách Khóa Học')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="fw-bold">Danh Sách Khóa Học</h1>
                <div class="dropdown">
                    <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-filter me-2"></i>Lọc khóa học
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="{{ route('courses.index') }}">Tất cả</a></li>
                        <li><a class="dropdown-item" href="{{ route('courses.index') }}?filter=featured">Nổi bật</a></li>
                        <li><a class="dropdown-item" href="{{ route('courses.index') }}?filter=popular">Phổ biến</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="{{ route('courses.index') }}?level=beginner">Cơ bản</a></li>
                        <li><a class="dropdown-item" href="{{ route('courses.index') }}?level=intermediate">Trung cấp</a></li>
                        <li><a class="dropdown-item" href="{{ route('courses.index') }}?level=advanced">Nâng cao</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @if($courses->count() > 0)
        <div class="row g-4">
            @foreach($courses as $course)
                <div class="col-md-6 col-lg-4">
                    <div class="card course-card h-100">
                        <div class="course-image position-relative">
                            <img src="{{ $course->thumbnail_url }}" class="card-img-top" alt="{{ $course->title }}" style="height: 200px; object-fit: cover;">
                            
                            @if($course->is_featured)
                                <div class="course-badge bg-warning">Nổi bật</div>
                            @endif
                            @if($course->is_popular)
                                <div class="course-badge bg-danger" style="top: 50px;">Phổ biến</div>
                            @endif
                            @if($course->discount_percentage > 0)
                                <div class="course-badge bg-success" style="top: 85px;">-{{ $course->discount_percentage }}%</div>
                            @endif
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <div class="mb-2">
                                <span class="badge bg-secondary">{{ $course->category->name ?? 'Chưa phân loại' }}</span>
                                <span class="badge bg-info ms-1">{{ $course->level }}</span>
                            </div>
                            
                            <h5 class="card-title fw-bold">{{ $course->title }}</h5>
                            <p class="card-text text-muted flex-grow-1">
                                {{ $course->short_description ?? Str::limit($course->description, 100) }}
                            </p>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    @if($course->sale_price)
                                        <span class="text-primary fw-bold h5 mb-0">{{ number_format($course->sale_price) }}₫</span>
                                        <small class="text-muted text-decoration-line-through ms-2">{{ number_format($course->price) }}₫</small>
                                    @else
                                        <span class="text-primary fw-bold h5 mb-0">{{ number_format($course->price) }}₫</span>
                                    @endif
                                </div>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>{{ $course->duration }} giờ
                                </small>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <small class="text-muted">
                                    <i class="fas fa-users me-1"></i>{{ $course->students_count }} học viên
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-star text-warning me-1"></i>{{ $course->rating }} ({{ $course->total_rating }})
                                </small>
                            </div>
                            
                            <div class="mt-auto">
                                @if(in_array($course->id, $enrolledCourses))
                                    <button class="btn btn-success w-100" disabled>
                                        <i class="fas fa-check me-2"></i>Đã đăng ký
                                    </button>
                                @else
                                    <form action="{{ route('courses.enroll', $course) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-plus me-2"></i>Đăng ký ngay
                                        </button>
                                    </form>
                                @endif
                                
                                <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-primary w-100 mt-2">
                                    <i class="fas fa-info-circle me-2"></i>Chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="row mt-5">
            <div class="col-12">
                {{ $courses->links() }}
            </div>
        </div>
    @else
        <div class="text-center py-5">
            <i class="fas fa-book fa-4x text-muted mb-3"></i>
            <h3 class="text-muted">Chưa có khóa học nào</h3>
            <p class="text-muted">Hiện tại không có khóa học nào để hiển thị.</p>
        </div>
    @endif
</div>

<style>
.course-badge {
    position: absolute;
    top: 15px;
    right: 15px;
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
}
</style>
@endsection