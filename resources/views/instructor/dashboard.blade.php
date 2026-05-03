@extends('layouts.app')

@section('title', 'Giảng viên - Dashboard')
@section('page-class', 'page-instructor-dashboard')

@push('styles')
    @vite('resources/css/pages/instructor/dashboard.css')
@endpush

@section('content')
<div class="container-fluid py-4">
    {{-- Hero --}}
    <div class="card border-0 shadow-sm instructor-hero-card mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <div class="text-white-50 small mb-1">Bảng điều khiển giảng viên</div>
                    <h2 class="text-white fw-bold mb-1">Xin chào, {{ auth()->user()->fullname ?? auth()->user()->username }}</h2>
                    <p class="text-white-50 mb-0">Theo dõi nhanh khóa học, lớp đang dạy và hoạt động học viên của bạn.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('instructor.classes.index') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-chalkboard-teacher me-1"></i>Lớp đang dạy
                    </a>
                    <a href="{{ route('instructor.courses.create') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-plus me-1"></i>Tạo khóa học
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick stats --}}
    <div class="row g-3 mb-4 instructor-stats-row">
        <div class="col-md-6 col-xl-3">
            <div class="stat-tile stat-tile-blue">
                <div class="stat-tile-icon"><i class="fas fa-book"></i></div>
                <div class="stat-tile-body">
                    <div class="stat-tile-value">{{ number_format($stats['total_courses']) }}</div>
                    <div class="stat-tile-label">Khóa học phụ trách</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-tile stat-tile-amber">
                <div class="stat-tile-icon"><i class="fas fa-question-circle"></i></div>
                <div class="stat-tile-body">
                    <div class="stat-tile-value">{{ number_format($stats['total_quizzes']) }}</div>
                    <div class="stat-tile-label">Bài kiểm tra</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-tile stat-tile-green">
                <div class="stat-tile-icon"><i class="fas fa-video"></i></div>
                <div class="stat-tile-body">
                    <div class="stat-tile-value">{{ number_format($stats['total_videos']) }}</div>
                    <div class="stat-tile-label">Video bài giảng</div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="stat-tile stat-tile-purple">
                <div class="stat-tile-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="stat-tile-body">
                    <div class="stat-tile-value">{{ number_format($stats['total_students']) }}</div>
                    <div class="stat-tile-label">Học viên đã duyệt</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('instructor.courses.index') }}" class="instructor-action-card">
                <div class="instructor-action-icon bg-primary-subtle text-primary"><i class="fas fa-book-open"></i></div>
                <div>
                    <div class="fw-bold">Quản lý khóa học</div>
                    <div class="small text-muted">Xem & chỉnh sửa</div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('instructor.classes.index') }}" class="instructor-action-card">
                <div class="instructor-action-icon bg-success-subtle text-success"><i class="fas fa-users"></i></div>
                <div>
                    <div class="fw-bold">Lớp & học viên</div>
                    <div class="small text-muted">Theo dõi tiến độ</div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('instructor.quizzes.index') }}" class="instructor-action-card">
                <div class="instructor-action-icon bg-warning-subtle text-warning"><i class="fas fa-clipboard-check"></i></div>
                <div>
                    <div class="fw-bold">Quiz & bài tập</div>
                    <div class="small text-muted">Tạo bài kiểm tra</div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-lg-3">
            <a href="{{ route('instructor.videos.index') }}" class="instructor-action-card">
                <div class="instructor-action-icon bg-danger-subtle text-danger"><i class="fas fa-photo-video"></i></div>
                <div>
                    <div class="fw-bold">Thư viện video</div>
                    <div class="small text-muted">Upload & quản lý</div>
                </div>
            </a>
        </div>
    </div>

    {{-- Main content: 2 columns --}}
    <div class="row g-4">
        <div class="col-xl-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="fas fa-book me-2 text-primary"></i>Khóa học gần đây</h5>
                        <small class="text-muted">Khóa học gắn với lớp bạn đang phụ trách.</small>
                    </div>
                    <a href="{{ route('instructor.courses.index') }}" class="btn btn-sm btn-outline-secondary">Xem tất cả</a>
                </div>
                <div class="card-body p-0">
                    @forelse($recentCourses as $course)
                        <div class="instructor-course-row d-flex justify-content-between align-items-center gap-3 px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="flex-grow-1 min-w-0">
                                <h6 class="mb-1 text-truncate">{{ $course->title }}</h6>
                                <div class="text-muted small mb-2">
                                    <i class="fas fa-folder me-1"></i>{{ $course->category->name ?? 'Chưa phân loại' }}
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <span class="badge bg-light text-dark border">{{ $course->delivery_mode_label }}</span>
                                    <span class="badge bg-light text-dark border">{{ $course->classes_count }} lớp</span>
                                    <span class="badge bg-light text-dark border">{{ $course->modules_count }} module</span>
                                </div>
                            </div>
                            <div class="d-flex flex-column flex-md-row gap-2">
                                <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-sm btn-outline-success">
                                    <i class="fas fa-eye me-1"></i>Chi tiết
                                </a>
                                <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit me-1"></i>Sửa
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="p-5 text-center">
                            <i class="fas fa-book fa-3x text-muted mb-3 opacity-50"></i>
                            <p class="text-muted mb-0">Bạn chưa có khóa học nào gắn với lớp đang dạy.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h5 class="fw-bold mb-1"><i class="fas fa-clipboard-check me-2 text-warning"></i>Quiz gần đây</h5>
                        <small class="text-muted">Hoạt động học viên mới nhất.</small>
                    </div>
                    <a href="{{ route('instructor.classes.index') }}" class="btn btn-sm btn-outline-secondary">Xem lớp</a>
                </div>
                <div class="card-body p-0">
                    @forelse($stats['recent_quiz_attempts'] as $attempt)
                        @php
                            $score = $attempt->score ?? 0;
                            $scoreColor = $score >= 80 ? 'success' : ($score >= 50 ? 'warning' : 'danger');
                        @endphp
                        <div class="px-4 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="fw-semibold text-truncate">{{ $attempt->user->fullname ?? $attempt->user->username ?? 'Học viên' }}</div>
                                    <div class="text-muted small text-truncate">{{ $attempt->quiz->title ?? 'Quiz' }}</div>
                                </div>
                                <span class="badge bg-{{ $scoreColor }}-subtle text-{{ $scoreColor }}-emphasis">{{ $score }} điểm</span>
                            </div>
                            <div class="text-muted small">
                                <i class="fas fa-clock me-1"></i>{{ optional($attempt->created_at)->format('d/m/Y H:i') }}
                                @if($attempt->quiz?->course)
                                    · {{ $attempt->quiz->course->title }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-5 text-center">
                            <i class="fas fa-clipboard fa-3x text-muted mb-3 opacity-50"></i>
                            <p class="text-muted mb-0">Chưa có lượt làm quiz nào gần đây.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
