@extends('layouts.admin')

@section('title', 'Thêm khóa học')
@section('page-title', 'Thêm khóa học')

@section('content')
@php
    $oldModules = old('modules', [['title' => '', 'description' => '', 'order' => 1]]);
    if (empty($oldModules)) {
        $oldModules = [['title' => '', 'description' => '', 'order' => 1]];
    }
    $oldClasses = old('classes', []);
    $instructorLabels = $instructors->mapWithKeys(fn ($instructor) => [
        (string) $instructor->id => trim($instructor->fullname . ' (' . $instructor->email . ')'),
    ])->all();
@endphp

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-plus-circle me-2"></i>Thêm khóa học mới
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

        <form action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

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
                                <label for="title" class="form-label fw-bold">
                                    Tên khóa học <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('title') is-invalid @enderror" id="title" name="title" value="{{ old('title') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="short_description" class="form-label fw-bold">
                                    Mô tả ngắn <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('short_description') is-invalid @enderror" id="short_description" name="short_description" rows="2" maxlength="500" required>{{ old('short_description') }}</textarea>
                                <div class="form-text"><span id="short_desc_counter">0</span>/500 ký tự</div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label fw-bold">
                                    Mô tả chi tiết <span class="text-danger">*</span>
                                </label>
                                <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="6" required>{{ old('description') }}</textarea>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="video_url" class="form-label fw-bold">Video giới thiệu</label>
                                    <input type="url" class="form-control @error('video_url') is-invalid @enderror" id="video_url" name="video_url" value="{{ old('video_url') }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="announcement" class="form-label fw-bold">Thông báo cho học viên</label>
                                    <textarea class="form-control @error('announcement') is-invalid @enderror" id="announcement" name="announcement" rows="2">{{ old('announcement') }}</textarea>
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
                                <small class="text-muted">Học viên sẽ thấy rõ các module ở trang chi tiết khóa học.</small>
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-module-btn">
                                <i class="fas fa-plus me-1"></i>Thêm module
                            </button>
                        </div>

                        <div class="card-body">
                            <div id="modules-container">
                                @foreach($oldModules as $index => $module)
                                    <div class="module-item border rounded p-3 mb-3" data-index="{{ $index }}">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Module {{ $index + 1 }}</h6>
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-module">Xóa module</button>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-7">
                                                <label class="form-label fw-semibold">Tên module</label>
                                                <input type="text" name="modules[{{ $index }}][title]" class="form-control" value="{{ $module['title'] ?? '' }}" placeholder="Ví dụ: Nghe, Nói, Đọc, Viết">
                                            </div>

                                            <div class="col-md-2">
                                                <label class="form-label fw-semibold">Thứ tự</label>
                                                <input type="number" name="modules[{{ $index }}][order]" class="form-control" value="{{ $module['order'] ?? ($index + 1) }}" min="0">
                                            </div>

                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Mô tả</label>
                                                <textarea name="modules[{{ $index }}][description]" class="form-control" rows="2" placeholder="Mô tả ngắn về kỹ năng hoặc chủ đề">{{ $module['description'] ?? '' }}</textarea>
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
                                <small class="text-muted">Giữ enroll theo class nhưng hiển thị theo nghĩa đợt học.</small>
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-class-btn">
                                <i class="fas fa-plus me-1"></i>Thêm đợt học
                            </button>
                        </div>

                        <div class="card-body">
                            <div id="classes-container">
                                @foreach($oldClasses as $index => $class)
                                    <div class="class-item border rounded p-3 mb-3" data-index="{{ $index }}">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Đợt học {{ $index + 1 }}</h6>
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-class">Xóa đợt học</button>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-5">
                                                <label class="form-label fw-semibold">Tên đợt học</label>
                                                <input type="text" name="classes[{{ $index }}][name]" class="form-control" value="{{ $class['name'] ?? '' }}" placeholder="Ví dụ: Khóa 01, Đợt tháng 06/2026">
                                            </div>

                                            <div class="col-md-4 instructor-lookup-group">
                                                <label class="form-label fw-semibold">Giảng viên</label>
                                                <input type="hidden" name="classes[{{ $index }}][instructor_id]" class="instructor-id-input" value="{{ $class['instructor_id'] ?? '' }}">
                                                <input
                                                    type="text"
                                                    class="form-control instructor-picker"
                                                    list="course-instructor-options"
                                                    value="{{ $instructorLabels[(string) ($class['instructor_id'] ?? '')] ?? '' }}"
                                                    placeholder="Gõ tên hoặc email giảng viên"
                                                    autocomplete="off"
                                                >
                                                <div class="form-text">Có thể nhập tên rồi chọn nhanh từ gợi ý.</div>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="form-label fw-semibold">Trạng thái</label>
                                                <select name="classes[{{ $index }}][status]" class="form-select">
                                                    <option value="active" {{ ($class['status'] ?? 'active') === 'active' ? 'selected' : '' }}>Mở đăng ký</option>
                                                    <option value="inactive" {{ ($class['status'] ?? '') === 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                                                </select>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Ngày bắt đầu</label>
                                                <input type="date" name="classes[{{ $index }}][start_date]" class="form-control" value="{{ $class['start_date'] ?? '' }}">
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Ngày kết thúc</label>
                                                <input type="date" name="classes[{{ $index }}][end_date]" class="form-control" value="{{ $class['end_date'] ?? '' }}">
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Sức chứa</label>
                                                <input type="number" name="classes[{{ $index }}][max_students]" class="form-control" value="{{ $class['max_students'] ?? 0 }}" min="0" placeholder="0 = không giới hạn">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Lịch học / lịch dự kiến</label>
                                                <textarea name="classes[{{ $index }}][schedule]" class="form-control" rows="2" placeholder="Ví dụ: Thứ 2, 4, 6 - 18:00 đến 20:00">{{ $class['schedule'] ?? '' }}</textarea>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-semibold">Địa điểm / link học</label>
                                                <textarea name="classes[{{ $index }}][meeting_info]" class="form-control" rows="2" placeholder="Ví dụ: Phòng A2 hoặc Zoom link">{{ $class['meeting_info'] ?? '' }}</textarea>
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-semibold">Giá riêng cho đợt học</label>
                                                <input type="number" name="classes[{{ $index }}][price_override]" class="form-control" value="{{ $class['price_override'] ?? '' }}" min="0" step="1000">
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
                                    <i class="fas fa-question-circle me-2"></i>Bài kiểm tra tự động
                                </h6>
                                <small class="text-muted">Tùy chọn, dùng để tạo nhanh quiz cơ bản cho khóa học.</small>
                            </div>

                            <button type="button" class="btn btn-outline-secondary btn-sm" id="add-quiz-question-admin">
                                <i class="fas fa-plus me-1"></i>Thêm câu hỏi
                            </button>
                        </div>

                        <div class="card-body">
                            <div id="quiz-questions-admin"></div>
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
                                <label for="category_id" class="form-label fw-bold">
                                    Nhóm ngành <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('category_id') is-invalid @enderror" id="category_id" name="category_id" required>
                                    <option value="">-- Chọn nhóm ngành --</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                    <option value="beginner" {{ old('level') === 'beginner' ? 'selected' : '' }}>Người mới</option>
                                    <option value="intermediate" {{ old('level') === 'intermediate' ? 'selected' : '' }}>Trung cấp</option>
                                    <option value="advanced" {{ old('level') === 'advanced' ? 'selected' : '' }}>Nâng cao</option>
                                    <option value="all" {{ old('level') === 'all' ? 'selected' : '' }}>Tất cả</option>
                                </select>
                            </div>

                            <div>
                                <label for="learning_type" class="form-label fw-bold">Hình thức đào tạo</label>
                                <select class="form-select" id="learning_type" name="learning_type" required>
                                    <option value="online" {{ old('learning_type', 'online') === 'online' ? 'selected' : '' }}>Online</option>
                                    <option value="offline" {{ old('learning_type') === 'offline' ? 'selected' : '' }}>Offline</option>
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
                                <input type="number" class="form-control" id="price" name="price" value="{{ old('price') }}" min="0" step="1000" required>
                            </div>

                            <div class="mb-3">
                                <label for="sale_price" class="form-label fw-bold">Giá khuyến mãi</label>
                                <input type="number" class="form-control" id="sale_price" name="sale_price" value="{{ old('sale_price') }}" min="0" step="1000">
                            </div>

                            <div>
                                <label for="duration" class="form-label fw-bold">Thời lượng ước tính (phút)</label>
                                <input type="number" class="form-control" id="duration" name="duration" value="{{ old('duration', 0) }}" min="0" step="1" readonly>
                                <div class="form-text">Hệ thống sẽ tự cộng thời lượng từ video, tài liệu, bài tập và quiz sau khi bạn thêm nội dung học tập.</div>
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
                            <div class="mb-3">
                                <label for="thumbnail" class="form-label fw-bold">Ảnh thumbnail</label>
                                <input type="file" class="form-control" id="thumbnail" name="thumbnail" accept="image/*">
                            </div>

                            <div class="mb-3">
                                <label for="banner_image" class="form-label fw-bold">Ảnh banner</label>
                                <input type="file" class="form-control" id="banner_image" name="banner_image" accept="image/*">
                            </div>

                            <div>
                                <label for="pdf" class="form-label fw-bold">PDF giới thiệu / tài liệu</label>
                                <input type="file" class="form-control" id="pdf" name="pdf" accept="application/pdf">
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
                                    <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Bản nháp</option>
                                    <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Xuất bản</option>
                                </select>
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_featured">Đánh dấu nổi bật</label>
                            </div>

                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="is_popular" name="is_popular" value="1" {{ old('is_popular') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_popular">Đánh dấu phổ biến</label>
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
                        <i class="fas fa-undo me-2"></i>Nhập lại
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Tạo khóa học
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
    const instructorOptions = Array.from(document.querySelectorAll('#course-instructor-options option'));

    let moduleIndex = modulesContainer.querySelectorAll('.module-item').length;
    let classIndex = classesContainer.querySelectorAll('.class-item').length;
    let questionIndex = 0;

    const getInstructorIdByLabel = function (label) {
        const normalizedLabel = label.trim();
        const matchedOption = instructorOptions.find((option) => option.value === normalizedLabel);
        return matchedOption ? matchedOption.dataset.id : '';
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
            };

            input.addEventListener('input', syncSelection);
            input.addEventListener('change', syncSelection);
            input.addEventListener('blur', syncSelection);
            syncSelection();
            input.dataset.lookupBound = '1';
        });
    };

    const scrollToNewestClass = function () {
        const newItem = classesContainer.querySelector('.class-item:last-child');
        if (!newItem) return;

        requestAnimationFrame(() => {
            newItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
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

    shortDescCounter.textContent = shortDescInput.value.length;
    shortDescInput.addEventListener('input', () => {
        shortDescCounter.textContent = shortDescInput.value.length;
    });

    bindInstructorLookups(classesContainer);

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
                        <input type="text" name="modules[${moduleIndex}][title]" class="form-control" placeholder="Ví dụ: Nghe, Nói, Đọc, Viết">
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
        classesContainer.insertAdjacentHTML('beforeend', `
            <div class="class-item border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">Đợt học ${classIndex + 1}</h6>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-class">Xóa đợt học</button>
                </div>
                <div class="row g-3">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Tên đợt học</label>
                        <input type="text" name="classes[${classIndex}][name]" class="form-control" placeholder="Ví dụ: Khóa 01">
                    </div>
                    <div class="col-md-4 instructor-lookup-group">
                        <label class="form-label fw-semibold">Giảng viên</label>
                        <input type="hidden" name="classes[${classIndex}][instructor_id]" class="instructor-id-input">
                        <input type="text" class="form-control instructor-picker" list="course-instructor-options" placeholder="Gõ tên hoặc email giảng viên" autocomplete="off">
                        <div class="form-text">Có thể nhập tên rồi chọn nhanh từ gợi ý.</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Trạng thái</label>
                        <select name="classes[${classIndex}][status]" class="form-select">
                            <option value="active">Mở đăng ký</option>
                            <option value="inactive">Tạm dừng</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ngày bắt đầu</label>
                        <input type="date" name="classes[${classIndex}][start_date]" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Ngày kết thúc</label>
                        <input type="date" name="classes[${classIndex}][end_date]" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Sức chứa</label>
                        <input type="number" name="classes[${classIndex}][max_students]" class="form-control" value="0" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Lịch học / lịch dự kiến</label>
                        <textarea name="classes[${classIndex}][schedule]" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Địa điểm / link học</label>
                        <textarea name="classes[${classIndex}][meeting_info]" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Giá riêng cho đợt học</label>
                        <input type="number" name="classes[${classIndex}][price_override]" class="form-control" min="0" step="1000">
                    </div>
                </div>
            </div>
        `);

        bindInstructorLookups(classesContainer.lastElementChild);
        classIndex += 1;
        scrollToNewestClass();
    });

    document.getElementById('add-quiz-question-admin').addEventListener('click', function () {
        quizContainer.insertAdjacentHTML('beforeend', `
            <div class="quiz-question border rounded p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0">Câu hỏi ${questionIndex + 1}</h6>
                    <button type="button" class="btn btn-outline-danger btn-sm remove-question">Xóa</button>
                </div>
                <input type="text" name="quiz_questions[${questionIndex}][question]" class="form-control mb-2" placeholder="Nội dung câu hỏi">
                <input type="text" name="quiz_questions[${questionIndex}][answer]" class="form-control" placeholder="Đáp án đúng">
            </div>
        `);

        questionIndex += 1;
    });

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('remove-module')) {
            const items = modulesContainer.querySelectorAll('.module-item');
            if (items.length > 1) {
                event.target.closest('.module-item').remove();
            }
        }

        if (event.target.classList.contains('remove-class')) {
            event.target.closest('.class-item').remove();
        }

        if (event.target.classList.contains('remove-question')) {
            event.target.closest('.quiz-question').remove();
        }
    });
});
</script>
@endsection