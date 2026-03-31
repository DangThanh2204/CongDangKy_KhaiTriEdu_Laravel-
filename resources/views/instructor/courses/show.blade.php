@extends('layouts.app')

@section('title', 'Xem trước: ' . $course->title)

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-eye me-2"></i>Xem trước khóa học</h2>
                <div>
                    <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-outline-primary me-2">
                        <i class="fas fa-edit"></i> Chỉnh sửa
                    </a>
                    <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Quay lại danh sách
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-body">
                    <div class="mb-3">
                        <span class="badge bg-secondary">{{ $course->category->name ?? 'Chưa phân loại' }}</span>
                        <span class="badge bg-{{ $course->status === 'published' ? 'success' : 'secondary' }} ms-1">
                            {{ $course->status === 'published' ? 'Công khai' : 'Nháp' }}
                        </span>
                    </div>

                    <h1 class="card-title fw-bold mb-3">{{ $course->title }}</h1>

                    @if($course->short_description)
                        <p class="lead text-muted mb-4">{{ $course->short_description }}</p>
                    @endif

                    @if($course->description)
                        <div class="mb-4">
                            <h4 class="fw-bold mb-3">Mô tả chi tiết</h4>
                            <div class="content">
                                {!! nl2br(e($course->description)) !!}
                            </div>
                        </div>
                    @endif

                    @if($course->video_url)
                        <div class="mb-4">
                            <h4 class="fw-bold mb-3">Video giới thiệu</h4>
                            <div class="ratio ratio-16x9">
                                <iframe src="{{ youtube_embed_url($course->video_url) }}" allowfullscreen></iframe>
                            </div>
                        </div>
                    @endif

                    @if($course->pdf_path)
                        <div class="mb-4">
                            <h4 class="fw-bold mb-3">Tài liệu PDF</h4>
                            <a href="{{ asset('storage/' . $course->pdf_path) }}" target="_blank" class="btn btn-outline-primary">
                                <i class="fas fa-file-pdf me-2"></i>Xem tài liệu PDF
                            </a>
                        </div>
                    @endif

                    @if($course->materials->count() > 0)
                        <div class="mb-4">
                            <h4 class="fw-bold mb-3">Nội dung khóa học</h4>
                            <div class="list-group">
                                @foreach($course->materials->sortBy('order') as $index => $material)
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-primary me-3">{{ $index + 1 }}</span>
                                            @if($material->type === 'video')
                                                <i class="fas fa-video text-primary me-2"></i>
                                            @elseif($material->type === 'pdf')
                                                <i class="fas fa-file-pdf text-danger me-2"></i>
                                            @elseif($material->type === 'quiz')
                                                <i class="fas fa-question-circle text-warning me-2"></i>
                                            @else
                                                <i class="fas fa-file text-secondary me-2"></i>
                                            @endif
                                            <div>
                                                <strong>{{ $material->title ?: 'Chưa có tiêu đề' }}</strong>
                                                <br><small class="text-muted">{{ ucfirst($material->type) }}</small>
                                            </div>
                                        </div>
                                        <div>
                                            @if($material->type === 'video' && isset($material->metadata['url']))
                                                <a href="{{ $material->metadata['url'] }}" target="_blank" class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="fas fa-external-link-alt"></i>
                                                </a>
                                            @elseif($material->type === 'pdf' && $material->file_path)
                                                <a href="{{ asset('storage/' . $material->file_path) }}" target="_blank" class="btn btn-sm btn-outline-danger me-1">
                                                    <i class="fas fa-file-pdf"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="mb-4">
                            <h4 class="fw-bold mb-3">Nội dung khóa học</h4>
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Khóa học này chưa có nội dung học tập nào.
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card sticky-top" style="top: 100px;">
                <div class="card-body">
                    <h4 class="fw-bold mb-3">Thông tin khóa học</h4>

                    <div class="mb-3">
                        <strong>Giá:</strong>
                        <span class="text-primary fw-bold ms-2">{{ number_format($course->price) }} VND</span>
                        @if($course->sale_price)
                            <br><small class="text-muted text-decoration-line-through">{{ number_format($course->sale_price) }} VND</small>
                        @endif
                    </div>

                    <div class="mb-3">
                        <strong>Trạng thái:</strong>
                        <span class="badge bg-{{ $course->status === 'published' ? 'success' : 'secondary' }} ms-2">
                            {{ $course->status === 'published' ? 'Công khai' : 'Nháp' }}
                        </span>
                    </div>

                    <div class="mb-3">
                        <strong>Danh mục:</strong>
                        <span class="ms-2">{{ $course->category->name ?? 'Chưa phân loại' }}</span>
                    </div>

                    <div class="mb-3">
                        <strong>Mã nhóm:</strong>
                        <span class="ms-2">{{ $course->series_key ?: 'Không có' }}</span>
                    </div>

                    <div class="mb-3">
                        <strong>Ngày tạo:</strong>
                        <span class="ms-2">{{ $course->created_at->format('d/m/Y H:i') }}</span>
                    </div>

                    <div class="mb-3">
                        <strong>Cập nhật:</strong>
                        <span class="ms-2">{{ $course->updated_at->format('d/m/Y H:i') }}</span>
                    </div>

                    <hr>

                    <div class="d-grid gap-2">
                        <a href="{{ route('instructor.courses.edit', $course) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa khóa học
                        </a>
                        <a href="{{ route('instructor.courses.materials.index', $course) }}" class="btn btn-outline-success">
                            <i class="fas fa-list me-2"></i>Quản lý nội dung
                        </a>
                        <a href="{{ route('instructor.courses.quiz.index', $course) }}" class="btn btn-outline-info">
                            <i class="fas fa-question-circle me-2"></i>Quản lý Quiz
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection