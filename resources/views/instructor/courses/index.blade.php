@extends('layouts.app')

@section('title', 'Quản lý khóa học')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Khóa học của tôi</h2>
                <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tạo khóa học mới
                </a>
            </div>

            @if($courses->count() > 0)
                <div class="row">
                    @foreach($courses as $course)
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title">{{ $course->title }}</h5>
                                    <p class="card-text text-muted">{{ \Illuminate\Support\Str::limit($course->short_description, 100) }}</p>
                                    <div class="mb-2">
                                        <span class="badge bg-{{ $course->status === 'published' ? 'success' : 'secondary' }}">
                                            {{ $course->status === 'published' ? 'Công khai' : 'Nháp' }}
                                        </span>
                                        <span class="badge bg-info">{{ number_format($course->price) }} VND</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('instructor.courses.show', $course) }}" class="btn btn-sm btn-outline-success me-1">
                                            <i class="fas fa-eye"></i> Xem
                                        </a>
                                        <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-sm btn-outline-primary me-1">
                                            <i class="fas fa-edit"></i> Sửa
                                        </a>
                                        <a href="{{ route('instructor.courses.quiz.index', $course) }}" class="btn btn-sm btn-outline-info me-1">
                                            <i class="fas fa-question-circle"></i> Quiz
                                        </a>
                                        <a href="{{ route('instructor.courses.enrollments.index', $course) }}" class="btn btn-sm btn-outline-dark">
                                            <i class="fas fa-users"></i> Học viên
                                        </a>
                                    </div>
                                </div>
                                <div class="card-footer text-muted">
                                    <small>Tạo: {{ $course->created_at->format('d/m/Y') }}</small>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-book-open fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">Chưa có khóa học nào</h4>
                    <p class="text-muted">Hãy tạo khóa học đầu tiên của bạn</p>
                    <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo khóa học mới
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection