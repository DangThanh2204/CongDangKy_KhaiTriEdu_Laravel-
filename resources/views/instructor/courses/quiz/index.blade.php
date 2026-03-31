@extends('layouts.app')

@section('title', 'Quản lý bài kiểm tra - ' . $course->title)

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Bài kiểm tra của khóa học: {{ $course->title }}</h1>
            <a href="{{ route('instructor.courses.edit', $course) }}" class="text-muted">← Quay lại chỉnh sửa khóa học</a>
        </div>
        <a href="{{ route('instructor.courses.quiz.create', $course) }}" class="btn btn-primary">Tạo bài kiểm tra mới</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($quizzes->count() > 0)
        <div class="row gy-3">
            @foreach($quizzes as $quiz)
                <div class="col-md-6">
                    <div class="card h-100">
                        <div class="card-body">
                            <h5 class="card-title">{{ $quiz->title }}</h5>
                            <p class="card-text text-muted">
                                {{ count($quiz->metadata['questions'] ?? []) }} câu hỏi
                            </p>
                            <p class="mb-1"><small class="text-muted">Cập nhật: {{ $quiz->updated_at->format('d/m/Y H:i') }}</small></p>
                        </div>
                        <div class="card-footer d-flex justify-content-between">
                            <a href="{{ route('instructor.courses.quiz.edit', [$course, $quiz]) }}" class="btn btn-outline-primary btn-sm">Chỉnh sửa</a>
                            <form method="POST" action="{{ route('instructor.courses.quiz.destroy', [$course, $quiz]) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" onclick="return confirm('Bạn có chắc muốn xóa bài kiểm tra này?')">Xóa</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="alert alert-info">Chưa có bài kiểm tra nào. <a href="{{ route('instructor.courses.quiz.create', $course) }}">Tạo bài kiểm tra đầu tiên</a></div>
    @endif
</div>
@endsection