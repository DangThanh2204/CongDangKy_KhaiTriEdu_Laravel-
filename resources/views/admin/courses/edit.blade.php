@extends('layouts.admin')

@section('title', 'Chỉnh sửa khóa học')
@section('page-title', 'Chỉnh sửa khóa học')

@section('content')
@php
    $moduleRows = old('modules', $course->modules->map(fn ($module) => [
        'id' => $module->id,
        'title' => $module->title,
        'description' => $module->description,
        'order' => $module->order,
    ])->values()->all());
    if (empty($moduleRows)) {
        $moduleRows = [['title' => '', 'description' => '', 'order' => 1]];
    }

    $classRows = old('classes', $course->classes->map(fn ($class) => [
        'id' => $class->id,
        'name' => $class->name,
        'instructor_id' => $class->instructor_id,
        'start_date' => optional($class->start_date)->format('Y-m-d'),
        'end_date' => optional($class->end_date)->format('Y-m-d'),
        'schedule' => $class->schedule,
        'meeting_info' => $class->meeting_info,
        'max_students' => $class->max_students,
        'price_override' => $class->price_override,
        'status' => $class->status,
    ])->values()->all());

    $instructorLabels = $instructors->mapWithKeys(fn ($instructor) => [
        (string) $instructor->id => trim($instructor->fullname . ' (' . $instructor->email . ')'),
    ])->all();

    $quizMaterial = $course->materials()->where('type', 'quiz')->first();
    $quizQuestions = old('quiz_questions', $quizMaterial?->metadata['questions'] ?? []);
@endphp

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-edit me-2"></i>
            Chỉnh sửa khóa học: <span class="text-primary">{{ $course->title }}</span>
        </h5>
    </div>

    <div class="card-body">
        @if($errors->any())
            <div class="alert alert-danger">
                <div class="fw-semibold mb-2">Vui lòng kiểm tra lại dữ liệu.</div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.courses.update', $course) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-info-circle me-2"></i>Thông tin cơ bản
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="title" class="form-label fw-bold">Tên khóa học</label>
                                <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $course->title) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="short_description" class="form-label fw-bold">Mô tả ngắn</label>
                                <textarea class="form-control" id="short_description" name="short_description" rows="2" maxlength="500" required>{{ old('short_description', $course->short_description) }}</textarea>
                                <div class="form-text"><span id="short_desc_counter">0</span>/500 ký tự</div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label fw-bold">Mô tả chi tiết</label>
                                <textarea class="form-control" id="description" name="description" rows="6" required>{{ old('description', $course->description) }}</textarea>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="video_url" class="form-label fw-bold">Video giới thiệu</label>
                                    <input type="url" class="form-control" id="video_url" name="video_url" value="{{ old('video_url', $course->video_url) }}">
                                </div>
                                <div class="col-md-6">
                                    <label for="announcement" class="form-label fw-bold">Thông báo cho học viên</label>
                                    <textarea class="form-control" id="announcement" name="announcement" rows="2">{{ old('announcement', $course->announcement) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-layer-group me-2"></i>Module / kỹ năng
                                </h6>
                                <small class="text-muted">Admin có thể tạo, cập nhật hoặc xóa module trực tiếp tại đây.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-module-btn">
                                <i class="fas fa-plus me-1"></i>Thêm module
                            </button>
                        </div>

                        <div class="card-body">
                            <div id="modules-container">
                                @foreach($moduleRows as $index => $module)
                                    <div class="module-item border rounded p-3 mb-3" data-index="{{ $index }}">
                                        @if(!empty($module['id']))
                                            <input type="hidden" name="modules[{{ $index }}][id]" value="{{ $module['id'] }}">
                                        @endif

                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Module {{ $index + 1 }}</h6>
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-module">Xóa module</button>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-7">
                                                <label class="form-label fw-semibold">Tên module</label>
                                                <input type="text" name="modules[{{ $index }}][title]" class="form-control" value="{{ $module['title'] ?? '' }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Thứ tự</label>
                                                <input type="number" name="modules[{{ $index }}][order]" class="form-control" value="{{ $module['order'] ?? ($index + 1) }}" min="0">
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Mô tả</label>
                                                <textarea name="modules[{{ $index }}][description]" class="form-control" rows="2">{{ $module['description'] ?? '' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-calendar-alt me-2"></i>Đợt học / khóa mở đăng ký
                                </h6>
                                <small class="text-muted">Nếu xóa đợt học đã có đăng ký, hệ thống sẽ chuyển sang trạng thái tạm dừng.</small>
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-class-btn">
                                <i class="fas fa-plus me-1"></i>Thêm đợt học
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="border rounded bg-light p-3 mb-3">
                                <div class="row g-3 align-items-end">
                                    <div class="col-lg-6">
                                        <label for="class-search-input" class="form-label fw-semibold mb-1">Tìm nhanh đợt học</label>
                                        <input
                                            type="text"
                                            id="class-search-input"
                                            class="form-control"
                                            placeholder="Gõ tên đợt, giảng viên, trạng thái hoặc lịch học"
                                            autocomplete="off"
                                        >
                                        <div class="form-text">Danh sách được thu gọn để quản lý nhiều đợt học mà không làm kéo dài toàn trang.</div>
                                    </div>

                                    <div class="col-lg-6">
                                        <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                                            <div class="badge text-bg-light border px-3 py-2">
                                                Đang hiển thị: <span id="class-visible-count">0</span>/<span id="class-total-count">0</span> đợt học
                                            </div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="class-expand-all">Mở tất cả</button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="class-collapse-all">Thu gọn</button>
                                            <button type="button" class="btn btn-outline-secondary btn-sm" id="class-search-reset">Xóa tìm kiếm</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="class-search-empty" class="alert alert-warning py-2 px-3 small d-none">
                                Không tìm thấy đợt học phù hợp với từ khóa hiện tại.
                            </div>

                            <div class="border rounded bg-white p-3" style="max-height: 72vh; overflow-y: auto;">
                                <div id="classes-container" class="pe-1">
                                    @foreach($classRows as $index => $class)
                                        <div class="class-item border rounded-3 shadow-sm p-3 mb-3 bg-white" data-index="{{ $index }}" data-collapsed="{{ $loop->first ? '0' : '1' }}">
                                            @if(!empty($class['id']))
                                                <input type="hidden" name="classes[{{ $index }}][id]" value="{{ $class['id'] }}">
                                            @endif

                                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 class-item-header">
                                                <div class="flex-grow-1">
                                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                        <span class="badge text-bg-light border">Đợt học {{ $index + 1 }}</span>
                                                        <span class="badge class-status-badge {{ ($class['status'] ?? 'active') === 'active' ? 'text-bg-success' : 'text-bg-secondary' }}">
                                                            {{ ($class['status'] ?? 'active') === 'active' ? 'Mở đăng ký' : 'Tạm dừng' }}
                                                        </span>
                                                    </div>
                                                    <div class="fw-semibold class-summary-title d-block text-truncate">
                                                        {{ $class['name'] ?: 'Đợt học chưa đặt tên' }}
                                                    </div>
                                                    <div class="small text-muted class-summary-meta d-block text-truncate">
                                                        Đang tải tóm tắt đợt học...
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-wrap gap-2">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm class-toggle-details">
                                                        <i class="fas {{ $loop->first ? 'fa-chevron-up' : 'fa-chevron-down' }} me-1"></i>
                                                        {{ $loop->first ? 'Thu gọn' : 'Mở chi tiết' }}
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-sm remove-class">Xóa đợt học</button>
                                                </div>
                                            </div>

                                            <div class="class-item-body border-top pt-3 mt-3 {{ $loop->first ? '' : 'd-none' }}">
                                                <div class="row g-3">
                                                    <div class="col-md-5">
                                                        <label class="form-label fw-semibold">Tên đợt học</label>
                                                        <input type="text" name="classes[{{ $index }}][name]" class="form-control class-name-input" value="{{ $class['name'] ?? '' }}">
                                                    </div>

                                                    <div class="col-md-4 instructor-lookup-group">
                                                        <label class="form-label fw-semibold">Giảng viên</label>
                                                        <input type="hidden" name="classes[{{ $index }}][instructor_id]" class="instructor-id-input" value="{{ $class['instructor_id'] ?? '' }}">
                                                        <input
                                                            type="text"
                                                            class="form-control instructor-picker class-instructor-input"
                                                            list="course-instructor-options"
                                                            value="{{ $instructorLabels[(string) ($class['instructor_id'] ?? '')] ?? '' }}"
                                                            placeholder="Gõ tên hoặc email giảng viên"
                                                            autocomplete="off"
                                                        >
                                                        <div class="form-text">Có thể nhập tên rồi chọn nhanh từ gợi ý.</div>
                                                    </div>

                                                    <div class="col-md-3">
                                                        <label class="form-label fw-semibold">Trạng thái</label>
                                                        <select name="classes[{{ $index }}][status]" class="form-select class-status-input">
                                                            <option value="active" {{ ($class['status'] ?? 'active') === 'active' ? 'selected' : '' }}>Mở đăng ký</option>
                                                            <option value="inactive" {{ ($class['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                                                        </select>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">Ngày bắt đầu</label>
                                                        <input type="date" name="classes[{{ $index }}][start_date]" class="form-control class-start-date-input" value="{{ $class['start_date'] ?? '' }}">
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">Ngày kết thúc</label>
                                                        <input type="date" name="classes[{{ $index }}][end_date]" class="form-control class-end-date-input" value="{{ $class['end_date'] ?? '' }}">
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">Sức chứa</label>
                                                        <input type="number" name="classes[{{ $index }}][max_students]" class="form-control class-capacity-input" value="{{ $class['max_students'] ?? 0 }}" min="0">
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Lịch học / lịch dự kiến</label>
                                                        <textarea name="classes[{{ $index }}][schedule]" class="form-control class-schedule-input" rows="2">{{ $class['schedule'] ?? '' }}</textarea>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Địa điểm / link học</label>
                                                        <textarea name="classes[{{ $index }}][meeting_info]" class="form-control class-meeting-input" rows="2">{{ $class['meeting_info'] ?? '' }}</textarea>
                                                    </div>

                                                    <div class="col-md-4">
                                                        <label class="form-label fw-semibold">Giá riêng cho đợt học</label>
                                                        <input type="number" name="classes[{{ $index }}][price_override]" class="form-control class-price-input" value="{{ $class['price_override'] ?? '' }}" min="0" step="1000">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-question-circle me-2"></i>Bài kiểm tra tự động
                                </h6>
                                <small class="text-muted">Quiz cơ bản gắn vào khóa học dưới dạng material type `quiz`.</small>
                            </div>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="add-quiz-question-admin">
                                <i class="fas fa-plus me-1"></i>Thêm câu hỏi
                            </button>
                        </div>

                        <div class="card-body">
                            <div id="quiz-questions-admin">
                                @foreach($quizQuestions as $index => $question)
                                    <div class="quiz-question border rounded p-3 mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0">Câu hỏi {{ $index + 1 }}</h6>
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-question">Xóa</button>
                                        </div>
                                        <input type="text" name="quiz_questions[{{ $index }}][question]" class="form-control mb-2" value="{{ $question['question'] ?? '' }}">
                                        <input type="text" name="quiz_questions[{{ $index }}][answer]" class="form-control" value="{{ $question['answer'] ?? '' }}">
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-tags me-2"></i>Phân loại
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="category_id" class="form-label fw-bold">Nhóm ngành</label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="">-- Chọn nhóm ngành --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $course->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <datalist id="course-instructor-options">
                                @foreach($instructors as $instructor)
                                    <option value="{{ $instructorLabels[(string) $instructor->id] }}" data-id="{{ $instructor->id }}"></option>
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-layer-group me-2"></i>Cấp độ & loại hình
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="level" class="form-label fw-bold">Cấp độ</label>
                                <select class="form-select" id="level" name="level" required>
                                    <option value="beginner" {{ old('level', $course->level) === 'beginner' ? 'selected' : '' }}>Người mới</option>
                                    <option value="intermediate" {{ old('level', $course->level) === 'intermediate' ? 'selected' : '' }}>Trung cấp</option>
                                    <option value="advanced" {{ old('level', $course->level) === 'advanced' ? 'selected' : '' }}>Nâng cao</option>
                                    <option value="all" {{ old('level', $course->level) === 'all' ? 'selected' : '' }}>Tất cả</option>
                                </select>
                            </div>

                            <div>
                                <label for="learning_type" class="form-label fw-bold">Hình thức đào tạo</label>
                                <select class="form-select" id="learning_type" name="learning_type" required>
                                    <option value="online" {{ old('learning_type', $course->learning_type) === 'online' ? 'selected' : '' }}>Online</option>
                                    <option value="offline" {{ old('learning_type', $course->learning_type) === 'offline' ? 'selected' : '' }}>Offline</option>
                                </select>
                                <div class="form-text">Khóa online được ghi danh tự động. Khóa offline dùng đợt học riêng và cần admin duyệt đăng ký.</div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-clock me-2"></i>Giá & thời lượng
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="price" class="form-label fw-bold">Giá gốc (VNĐ)</label>
                                <input type="number" class="form-control" id="price" name="price" value="{{ old('price', $course->price) }}" min="0" step="1000" required>
                            </div>

                            <div class="mb-3">
                                <label for="sale_price" class="form-label fw-bold">Giá khuyến mãi</label>
                                <input type="number" class="form-control" id="sale_price" name="sale_price" value="{{ old('sale_price', $course->sale_price) }}" min="0" step="1000">
                            </div>

                            <div>
                                <label for="duration" class="form-label fw-bold">Thời lượng ước tính (phút)</label>
                                <input type="number" class="form-control" id="duration" name="duration" value="{{ old('duration', $course->estimated_duration_minutes) }}" min="0" step="1" readonly>
                                <div class="form-text">Thời lượng này được tự tính từ toàn bộ nội dung học tập đang gắn trong khóa học.</div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-images me-2"></i>Hình ảnh & tài liệu
                            </h6>
                        </div>
                        <div class="card-body">
                            @if($course->thumbnail)
                                <div class="mb-2">
                                    <img src="{{ $course->thumbnail_url }}" alt="Thumbnail" class="img-fluid rounded">
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="thumbnail" class="form-label fw-bold">Ảnh thumbnail</label>
                                <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                            </div>

                            @if($course->banner_image)
                                <div class="mb-2">
                                    <img src="{{ $course->banner_image_url }}" alt="Banner" class="img-fluid rounded">
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="banner_image" class="form-label fw-bold">Ảnh banner</label>
                                <input type="file" class="form-control" id="banner_image" name="banner_image" accept="image/*">
                            </div>

                            <div>
                                <label for="pdf" class="form-label fw-bold">PDF giới thiệu / tài liệu</label>
                                <input type="file" class="form-control" id="pdf" name="pdf" accept="application/pdf">
                                @if($course->pdf_path)
                                    <div class="small mt-2">
                                        <a href="{{ asset('storage/' . $course->pdf_path) }}" target="_blank">Xem tệp PDF hiện tại</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0">
                                <i class="fas fa-eye me-2"></i>Trạng thái & hiển thị
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="status" class="form-label fw-bold">Trạng thái</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="draft" {{ old('status', $course->status) === 'draft' ? 'selected' : '' }}>Bản nháp</option>
                                    <option value="published" {{ old('status', $course->status) === 'published' ? 'selected' : '' }}>Xuất bản</option>
                                </select>
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $course->is_featured) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">Đánh dấu nổi bật</label>
                            </div>

                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular" value="1" {{ old('is_popular', $course->is_popular) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_popular">Đánh dấu phổ biến</label>
                            </div>

                            <div class="small text-muted d-grid gap-1">
                                <div><strong>Học viên:</strong> {{ $course->students_count }}</div>
                                <div><strong>Đánh giá:</strong> {{ $course->rating }} ({{ $course->total_rating }})</div>
                                <div><strong>Ngày tạo:</strong> {{ $course->created_at->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>

                <div>
                    <button type="reset" class="btn btn-outline-danger me-2">
                        <i class="fas fa-undo me-2"></i>Hoàn tác
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Cập nhật khóa học
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const shortDescInput = document.getElementById('short_description');
    const shortDescCounter = document.getElementById('short_desc_counter');
    const priceInput = document.getElementById('price');
    const salePriceInput = document.getElementById('sale_price');
    const modulesContainer = document.getElementById('modules-container');
    const classesContainer = document.getElementById('classes-container');
    const quizContainer = document.getElementById('quiz-questions-admin');
    const classSearchInput = document.getElementById('class-search-input');
    const classSearchReset = document.getElementById('class-search-reset');
    const classExpandAll = document.getElementById('class-expand-all');
    const classCollapseAll = document.getElementById('class-collapse-all');
    const classVisibleCount = document.getElementById('class-visible-count');
    const classTotalCount = document.getElementById('class-total-count');
    const classSearchEmpty = document.getElementById('class-search-empty');
    const instructorOptions = Array.from(document.querySelectorAll('#course-instructor-options option'));

    let moduleIndex = modulesContainer.querySelectorAll('.module-item').length;
    let classIndex = classesContainer.querySelectorAll('.class-item').length;
    let questionIndex = quizContainer.querySelectorAll('.quiz-question').length;

    const getInstructorIdByLabel = function (label) {
        const normalizedLabel = label.trim();
        const matchedOption = instructorOptions.find((option) => option.value === normalizedLabel);
        return matchedOption ? matchedOption.dataset.id : '';
    };

    const normalizeSearchText = function (value) {
        return (value || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    };

    const formatDateLabel = function (value) {
        if (!value) {
            return '';
        }

        const date = new Date(value);
        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return new Intl.DateTimeFormat('vi-VN').format(date);
    };

    const getStatusBadgeClass = function (status) {
        return status === 'active' ? 'text-bg-success' : 'text-bg-secondary';
    };

    const getActiveClassItems = function () {
        return Array.from(classesContainer.querySelectorAll('.class-item')).filter((item) => !item.classList.contains('d-none'));
    };

    const setClassCollapsed = function (item, collapsed) {
        const body = item.querySelector('.class-item-body');
        const toggle = item.querySelector('.class-toggle-details');

        if (!body || !toggle) {
            return;
        }

        item.dataset.collapsed = collapsed ? '1' : '0';
        body.classList.toggle('d-none', collapsed);
        toggle.innerHTML = collapsed
            ? '<i class="fas fa-chevron-down me-1"></i>Mở chi tiết'
            : '<i class="fas fa-chevron-up me-1"></i>Thu gọn';

        item.classList.toggle('border-primary-subtle', !collapsed);
    };

    const updateClassCardSummary = function (item) {
        const nameInput = item.querySelector('.class-name-input');
        const instructorInput = item.querySelector('.class-instructor-input');
        const statusSelect = item.querySelector('.class-status-input');
        const startDateInput = item.querySelector('.class-start-date-input');
        const endDateInput = item.querySelector('.class-end-date-input');
        const capacityInput = item.querySelector('.class-capacity-input');
        const title = item.querySelector('.class-summary-title');
        const meta = item.querySelector('.class-summary-meta');
        const badge = item.querySelector('.class-status-badge');

        const name = nameInput?.value.trim() || 'Đợt học chưa đặt tên';
        const instructor = instructorInput?.value.trim() || 'Chưa chọn giảng viên';
        const statusValue = statusSelect?.value || 'inactive';
        const statusText = statusSelect?.options[statusSelect.selectedIndex]?.text?.trim() || 'Chưa có trạng thái';
        const start = formatDateLabel(startDateInput?.value || '');
        const end = formatDateLabel(endDateInput?.value || '');
        const capacityValue = parseInt(capacityInput?.value || '0', 10);
        const capacityText = capacityValue > 0 ? `${capacityValue} chỗ` : 'Không giới hạn';
        const dateRange = start || end
            ? `${start || 'Chưa có ngày bắt đầu'} - ${end || 'Chưa có ngày kết thúc'}`
            : 'Chưa lên lịch ngày học';

        if (title) {
            title.textContent = name;
        }

        if (meta) {
            meta.textContent = [instructor, statusText, dateRange, capacityText].join(' | ');
        }

        if (badge) {
            badge.textContent = statusText;
            badge.className = `badge class-status-badge ${getStatusBadgeClass(statusValue)}`;
        }
    };

    const bindInstructorLookups = function (scope = document) {
        scope.querySelectorAll('.instructor-picker').forEach((input) => {
            if (input.dataset.lookupBound === '1') {
                return;
            }

            const group = input.closest('.instructor-lookup-group');
            const hiddenInput = group ? group.querySelector('.instructor-id-input') : null;

            if (!hiddenInput) {
                return;
            }

            const syncSelection = function () {
                hiddenInput.value = getInstructorIdByLabel(input.value);
                const classItem = input.closest('.class-item');
                if (classItem) {
                    updateClassCardSummary(classItem);
                    if (classSearchInput.value.trim()) {
                        applyClassFilters();
                    }
                }
            };

            input.addEventListener('input', syncSelection);
            input.addEventListener('change', syncSelection);
            input.addEventListener('blur', syncSelection);
            syncSelection();
            input.dataset.lookupBound = '1';
        });
    };

    const initializeClassCard = function (item, defaultCollapsed = true) {
        const collapsed = item.dataset.collapsed ? item.dataset.collapsed === '1' : defaultCollapsed;
        setClassCollapsed(item, collapsed);
        updateClassCardSummary(item);
    };

    const buildClassSearchText = function (item) {
        const values = [
            item.querySelector('.class-summary-title')?.textContent || '',
            item.querySelector('.class-summary-meta')?.textContent || '',
        ];

        item.querySelectorAll('input, textarea, select').forEach((field) => {
            if (field.type === 'hidden') {
                return;
            }

            if (field.tagName === 'SELECT') {
                values.push(field.options[field.selectedIndex]?.text || field.value || '');
                return;
            }

            values.push(field.value || '');
        });

        return normalizeSearchText(values.join(' '));
    };

    const updateClassManagementSummary = function () {
        const items = getActiveClassItems();
        const visibleItems = items.filter((item) => item.style.display !== 'none');

        classTotalCount.textContent = items.length;
        classVisibleCount.textContent = visibleItems.length;
        classSearchReset.disabled = !classSearchInput.value.trim();
        classExpandAll.disabled = visibleItems.length === 0;
        classCollapseAll.disabled = visibleItems.length === 0;
        classSearchEmpty.classList.toggle('d-none', visibleItems.length > 0 || items.length === 0);
    };

    const applyClassFilters = function () {
        const keyword = normalizeSearchText(classSearchInput.value);

        getActiveClassItems().forEach((item) => {
            updateClassCardSummary(item);
            const matched = !keyword || buildClassSearchText(item).includes(keyword);
            item.style.display = matched ? '' : 'none';

            if (matched && keyword) {
                setClassCollapsed(item, false);
            }
        });

        updateClassManagementSummary();
    };

    const createClassItemTemplate = function (index) {
        return `
            <div class="class-item border rounded-3 shadow-sm p-3 mb-3 bg-white" data-index="${index}" data-collapsed="0">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 class-item-header">
                    <div class="flex-grow-1">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <span class="badge text-bg-light border">Đợt học ${index + 1}</span>
                            <span class="badge class-status-badge text-bg-success">Mở đăng ký</span>
                        </div>
                        <div class="fw-semibold class-summary-title d-block text-truncate">Đợt học chưa đặt tên</div>
                        <div class="small text-muted class-summary-meta d-block text-truncate">Đang tải tóm tắt đợt học...</div>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm class-toggle-details">
                            <i class="fas fa-chevron-up me-1"></i>Thu gọn
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-class">Xóa đợt học</button>
                    </div>
                </div>

                <div class="class-item-body border-top pt-3 mt-3">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Tên đợt học</label>
                            <input type="text" name="classes[${index}][name]" class="form-control class-name-input">
                        </div>

                        <div class="col-md-4 instructor-lookup-group">
                            <label class="form-label fw-semibold">Giảng viên</label>
                            <input type="hidden" name="classes[${index}][instructor_id]" class="instructor-id-input">
                            <input type="text" class="form-control instructor-picker class-instructor-input" list="course-instructor-options" placeholder="Gõ tên hoặc email giảng viên" autocomplete="off">
                            <div class="form-text">Có thể nhập tên rồi chọn nhanh từ gợi ý.</div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Trạng thái</label>
                            <select name="classes[${index}][status]" class="form-select class-status-input">
                                <option value="active">Mở đăng ký</option>
                                <option value="inactive">Tạm dừng</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ngày bắt đầu</label>
                            <input type="date" name="classes[${index}][start_date]" class="form-control class-start-date-input">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Ngày kết thúc</label>
                            <input type="date" name="classes[${index}][end_date]" class="form-control class-end-date-input">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sức chứa</label>
                            <input type="number" name="classes[${index}][max_students]" class="form-control class-capacity-input" value="0" min="0">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Lịch học / lịch dự kiến</label>
                            <textarea name="classes[${index}][schedule]" class="form-control class-schedule-input" rows="2"></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Địa điểm / link học</label>
                            <textarea name="classes[${index}][meeting_info]" class="form-control class-meeting-input" rows="2"></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Giá riêng cho đợt học</label>
                            <input type="number" name="classes[${index}][price_override]" class="form-control class-price-input" min="0" step="1000">
                        </div>
                    </div>
                </div>
            </div>
        `;
    };

    const scrollToNewestClass = function () {
        const newItem = classesContainer.querySelector('.class-item:last-child');
        if (!newItem) return;

        requestAnimationFrame(() => {
            newItem.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            const firstField = newItem.querySelector('input, textarea, select');
            if (!firstField) return;

            window.setTimeout(() => {
                try {
                    firstField.focus({ preventScroll: true });
                } catch (error) {
                    firstField.focus();
                }
            }, 180);
        });
    };

    const handleClassFieldMutation = function (event) {
        const classItem = event.target.closest('.class-item');
        if (!classItem) {
            return;
        }

        updateClassCardSummary(classItem);

        if (classSearchInput.value.trim()) {
            applyClassFilters();
        } else {
            updateClassManagementSummary();
        }
    };

    shortDescCounter.textContent = shortDescInput.value.length;
    shortDescInput.addEventListener('input', () => {
        shortDescCounter.textContent = shortDescInput.value.length;
    });

    bindInstructorLookups(classesContainer);
    getActiveClassItems().forEach((item, index) => initializeClassCard(item, index !== 0));
    applyClassFilters();

    classSearchInput.addEventListener('input', applyClassFilters);

    classSearchReset.addEventListener('click', function () {
        classSearchInput.value = '';
        applyClassFilters();
        classSearchInput.focus();
    });

    classExpandAll.addEventListener('click', function () {
        getActiveClassItems().forEach((item) => {
            if (item.style.display !== 'none') {
                setClassCollapsed(item, false);
            }
        });
    });

    classCollapseAll.addEventListener('click', function () {
        getActiveClassItems().forEach((item) => {
            if (item.style.display !== 'none') {
                setClassCollapsed(item, true);
            }
        });
    });

    classesContainer.addEventListener('input', handleClassFieldMutation);
    classesContainer.addEventListener('change', handleClassFieldMutation);

    salePriceInput.addEventListener('input', function () {
        if (this.value && priceInput.value && parseFloat(this.value) >= parseFloat(priceInput.value)) {
            this.setCustomValidity('Giá khuyến mãi phải nhỏ hơn giá gốc.');
        } else {
            this.setCustomValidity('');
        }
    });

    document.getElementById('add-module-btn').addEventListener('click', function () {
        modulesContainer.insertAdjacentHTML('beforeend', `
            <div class="module-item border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Module ${moduleIndex + 1}</h6>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-module">Xóa module</button>
                </div>
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label fw-semibold">Tên module</label>
                        <input type="text" name="modules[${moduleIndex}][title]" class="form-control">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Thứ tự</label>
                        <input type="number" name="modules[${moduleIndex}][order]" class="form-control" value="${moduleIndex + 1}" min="0">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Mô tả</label>
                        <textarea name="modules[${moduleIndex}][description]" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
        `);

        moduleIndex += 1;
    });

    document.getElementById('add-class-btn').addEventListener('click', function () {
        classesContainer.insertAdjacentHTML('beforeend', createClassItemTemplate(classIndex));
        const newItem = classesContainer.lastElementChild;

        bindInstructorLookups(newItem);
        initializeClassCard(newItem, false);
        classSearchInput.value = '';
        classIndex += 1;
        applyClassFilters();
        scrollToNewestClass();
    });

    document.getElementById('add-quiz-question-admin').addEventListener('click', function () {
        quizContainer.insertAdjacentHTML('beforeend', `
            <div class="quiz-question border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Câu hỏi ${questionIndex + 1}</h6>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-question">Xóa</button>
                </div>
                <input type="text" name="quiz_questions[${questionIndex}][question]" class="form-control mb-2">
                <input type="text" name="quiz_questions[${questionIndex}][answer]" class="form-control">
            </div>
        `);

        questionIndex += 1;
    });

    document.addEventListener('click', function (event) {
        const toggleButton = event.target.closest('.class-toggle-details');
        if (toggleButton) {
            const item = toggleButton.closest('.class-item');
            setClassCollapsed(item, item.dataset.collapsed !== '1');
            return;
        }

        if (event.target.classList.contains('remove-module')) {
            const item = event.target.closest('.module-item');
            const idInput = item.querySelector('input[name$="[id]"]');

            if (idInput) {
                const prefix = idInput.name.replace(/\[id\]$/, '');
                let destroyInput = item.querySelector(`input[name="${prefix}[_destroy]"]`);

                if (!destroyInput) {
                    destroyInput = document.createElement('input');
                    destroyInput.type = 'hidden';
                    destroyInput.name = `${prefix}[_destroy]`;
                    destroyInput.value = '1';
                    item.appendChild(destroyInput);
                }

                item.classList.add('d-none');
            } else {
                item.remove();
            }
        }

        if (event.target.classList.contains('remove-class')) {
            const item = event.target.closest('.class-item');
            const idInput = item.querySelector('input[name$="[id]"]');

            if (idInput) {
                const prefix = idInput.name.replace(/\[id\]$/, '');
                let destroyInput = item.querySelector(`input[name="${prefix}[_destroy]"]`);

                if (!destroyInput) {
                    destroyInput = document.createElement('input');
                    destroyInput.type = 'hidden';
                    destroyInput.name = `${prefix}[_destroy]`;
                    destroyInput.value = '1';
                    item.appendChild(destroyInput);
                }

                item.classList.add('d-none');
            } else {
                item.remove();
            }

            applyClassFilters();
        }

        if (event.target.classList.contains('remove-question')) {
            event.target.closest('.quiz-question').remove();
        }
    });
});
</script>
@endsection