@extends('layouts.app')

@section('title', 'Giảng viên - Dashboard')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Bảng điều khiển giảng viên</h1>
        <a href="{{ route('instructor.courses.create') }}" class="btn btn-primary">Tạo khóa học mới</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @php
        $myCourses = $courses->filter(fn($course) => $course->instructor_id === auth()->id());
    @endphp

    @if($myCourses->count() > 0)
        <div class="row gy-3">
            @foreach($myCourses as $course)
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $course->title }}</h5>
                            <p class="card-text text-muted">{{ Str::limit($course->short_description, 140) }}</p>
                            <p class="mb-1"><strong>Giá:</strong> {{ number_format($course->final_price) }}₫</p>
                            <p class="mb-1"><strong>Trạng thái:</strong> {{ ucfirst($course->status) }}</p>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-primary btn-sm">Xem</a>
                            <div>
                                <a href="{{ route('instructor.courses.quiz.index', $course) }}" class="btn btn-outline-info btn-sm me-1">Quản lý Quiz</a>
                                <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-secondary btn-sm">Chỉnh sửa</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">Bạn chưa có khóa học nào. Bắt đầu tạo ngay!</div>
    @endif
</div>
@endsection
