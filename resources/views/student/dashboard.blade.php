{{-- File: resources/views/student/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Kiến thức là chìa khóa của thành công</h4>
                    <a href="{{ route('courses.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>Đăng ký khóa học mới
                    </a>
                </div>
                <div class="card-body">
                    <!-- Welcome Section -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="border rounded p-4" style="background: var(--gradient-primary); color: white;">
                                <h5 class="fw-bold mb-2">Xin chào, <strong>{{ $user->fullname }}</strong></h5>
                                <p class="mb-0">Chào mừng bạn đến với trang quản lý khóa học</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="border rounded p-3 feature-card">
                                        <h5 class="fw-bold text-primary mb-1">{{ $approvedCourses->count() }}</h5>
                                        <small class="text-muted">Đã đăng ký</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-3 feature-card">
                                        <h5 class="fw-bold text-warning mb-1">{{ $pendingCourses->count() }}</h5>
                                        <small class="text-muted">Chờ duyệt</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="border rounded p-3 feature-card">
                                        <h5 class="fw-bold text-success mb-1">{{ $enrollments->where('status', 'completed')->count() }}</h5>
                                        <small class="text-muted">Hoàn thành</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách khóa học dạng Card -->
                    <div class="row">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="mb-0">Khóa học của tôi</h5>
                                <span class="badge bg-primary px-3 py-2">{{ $enrollments->count() }} khóa học</span>
                            </div>
                            
                            @if($enrollments->count() > 0)
                                <div class="row">
                                    @foreach($enrollments as $enrollment)
                                        <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                                            <div class="card course-card h-100">
                                                <div class="course-image position-relative">
                                                    <!-- Sửa phần hiển thị ảnh -->
                                                    @if($enrollment->course->thumbnail_url && filter_var($enrollment->course->thumbnail_url, FILTER_VALIDATE_URL))
                                                        <img src="{{ $enrollment->course->thumbnail_url }}" 
                                                             alt="{{ $enrollment->course->title }}" 
                                                             class="card-img-top"
                                                             style="height: 200px; object-fit: cover;"
                                                             onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZmZmIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxNCIgZmlsbD0iIzY2NiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPuWbvuWDj+WbvueJhzwvdGV4dD48L3N2Zz4='">
                                                    @else
                                                        <div class="card-img-top d-flex align-items-center justify-content-center bg-primary text-white" 
                                                             style="height: 200px; background: var(--gradient-primary) !important;">
                                                            <i class="fas fa-book fa-3x"></i>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="card-body d-flex flex-column">
                                                    <h6 class="card-title fw-bold mb-2">
                                                        <a href="{{ route('courses.show', $enrollment->course) }}" 
                                                           class="text-decoration-none text-dark">
                                                            {{ $enrollment->course->title }}
                                                        </a>
                                                    </h6>
                                                    <p class="text-muted small mb-3">
                                                        <i class="fas fa-tag me-1"></i>
                                                        {{ $enrollment->course->category->name ?? 'Chưa phân loại' }}
                                                    </p>
                                                    <div class="mt-auto">
                                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                                            <small class="text-muted">
                                                                <i class="fas fa-calendar me-1"></i>
                                                                {{ $enrollment->created_at->format('d/m/Y') }}
                                                            </small>
                                                            @if($enrollment->isApproved())
                                                                <span class="badge bg-success">
                                                                    <i class="fas fa-check me-1"></i>Đã duyệt
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <div class="d-flex gap-2">
                                                            @if($enrollment->isApproved())
                                                                <a href="#" class="btn btn-success btn-sm flex-fill">
                                                                    <i class="fas fa-play me-1"></i>Vào học
                                                                </a>
                                                            @endif
                                                            
                                                            @if(!$enrollment->isCompleted())
                                                                <form action="{{ route('courses.unenroll', $enrollment->course) }}" 
                                                                      method="POST" class="flex-fill">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-outline-danger btn-sm w-100" 
                                                                            onclick="return confirm('Bạn có chắc muốn hủy đăng ký?')">
                                                                        Hủy
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <div class="feature-icon mx-auto mb-3">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <h4 class="text-muted">Bạn chưa đăng ký khóa học nào</h4>
                                    <p class="text-muted mb-4">Hãy khám phá và đăng ký khóa học phù hợp với bạn</p>
                                    <a href="{{ route('courses.index') }}" class="btn btn-primary btn-lg">
                                        <i class="fas fa-search me-2"></i>Khám phá khóa học
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-5">
                        <div class="col-12">
                            <h5 class="mb-4">Hành động nhanh</h5>
                            <div class="row">
                                <div class="col-xl-3 col-md-6 mb-3">
                                    <a href="{{ route('courses.index') }}" class="card feature-card text-decoration-none h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="feature-icon mx-auto mb-3">
                                                <i class="fas fa-search"></i>
                                            </div>
                                            <h6 class="fw-bold mb-2">Tìm khóa học</h6>
                                            <p class="text-muted small mb-0">Khám phá các khóa học mới</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-xl-3 col-md-6 mb-3">
                                    <a href="#" class="card feature-card text-decoration-none h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="feature-icon mx-auto mb-3" style="background: var(--gradient-accent);">
                                                <i class="fas fa-chart-line"></i>
                                            </div>
                                            <h6 class="fw-bold mb-2">Tiến độ học</h6>
                                            <p class="text-muted small mb-0">Theo dõi quá trình học tập</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-xl-3 col-md-6 mb-3">
                                    <a href="#" class="card feature-card text-decoration-none h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="feature-icon mx-auto mb-3" style="background: var(--gradient-primary);">
                                                <i class="fas fa-certificate"></i>
                                            </div>
                                            <h6 class="fw-bold mb-2">Chứng chỉ</h6>
                                            <p class="text-muted small mb-0">Xem chứng chỉ đã đạt được</p>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-xl-3 col-md-6 mb-3">
                                    <a href="#" class="card feature-card text-decoration-none h-100">
                                        <div class="card-body text-center p-4">
                                            <div class="feature-icon mx-auto mb-3" style="background: var(--gradient-accent);">
                                                <i class="fas fa-cog"></i>
                                            </div>
                                            <h6 class="fw-bold mb-2">Cài đặt</h6>
                                            <p class="text-muted small mb-0">Quản lý tài khoản cá nhân</p>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection