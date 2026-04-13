@extends('layouts.app')

@section('title', 'Giảng viên - Dashboard')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Bảng điều khiển giảng viên</h1>
            <p class="text-muted mb-0">Theo dõi nhanh khóa học, lớp đang dạy và hoạt động học viên của bạn.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('instructor.classes.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-users-class me-2"></i>Lớp tôi đang dạy
            </a>
            <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tạo khóa học mới
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small mb-2">Khóa học phụ trách</div>
                    <div class="display-6 fw-bold">{{ number_format($stats['total_courses']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small mb-2">Bài kiểm tra</div>
                    <div class="display-6 fw-bold">{{ number_format($stats['total_quizzes']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small mb-2">Video / học liệu video</div>
                    <div class="display-6 fw-bold">{{ number_format($stats['total_videos']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <div class="text-muted small mb-2">Học viên đã duyệt</div>
                    <div class="display-6 fw-bold">{{ number_format($stats['total_students']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Khóa học gần đây</h5>
                        <small class="text-muted">Các khóa học bạn đang phụ trách thông qua lớp đang dạy.</small>
                    </div>
                    <a href="{{ route('instructor.courses.index') }}" class="btn btn-sm btn-outline-secondary">Xem tất cả</a>
                </div>
                <div class="card-body">
                    @forelse($recentCourses as $course)
                        <div class="d-flex justify-content-between align-items-start gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div>
                                <h6 class="mb-1">{{ $course->title }}</h6>
                                <div class="text-muted small mb-2">{{ $course->category->name ?? 'Chưa phân loại' }}</div>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-light text-dark border">{{ $course->delivery_mode_label }}</span>
                                    <span class="badge bg-light text-dark border">{{ $course->classes_count }} lớp</span>
                                    <span class="badge bg-light text-dark border">{{ $course->modules_count }} module</span>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2 justify-content-end">
                                <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-sm btn-outline-success">Chi tiết</a>
                                <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-sm btn-outline-primary">Chỉnh sửa</a>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info mb-0">Bạn chưa có khóa học nào gắn với lớp đang dạy.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1">Lần làm quiz gần đây</h5>
                        <small class="text-muted">Giúp giảng viên theo dõi nhanh mức độ tương tác của học viên.</small>
                    </div>
                    <a href="{{ route('instructor.classes.index') }}" class="btn btn-sm btn-outline-primary">Xem lớp học</a>
                </div>
                <div class="card-body">
                    @forelse($stats['recent_quiz_attempts'] as $attempt)
                        <div class="py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="fw-semibold">{{ $attempt->user->fullname ?? $attempt->user->username ?? 'Học viên' }}</div>
                            <div class="text-muted small">{{ $attempt->quiz->title ?? 'Quiz' }} · {{ $attempt->quiz->course->title ?? 'Khóa học' }}</div>
                            <div class="small mt-1">
                                <span class="badge bg-light text-dark border me-2">Điểm: {{ $attempt->score ?? 0 }}</span>
                                <span class="text-muted">{{ optional($attempt->created_at)->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-light border mb-0">Chưa có lượt làm quiz nào gần đây.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
