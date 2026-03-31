@extends('layouts.admin')

@section('title', 'Thêm đợt học')
@section('page-title', 'Thêm đợt học')

@section('content')
@php
    $selectedCourseId = old('course_id', request('course_id'));
    $selectedInstructorId = old('instructor_id');
    $selectedCourse = $courses->firstWhere('id', $selectedCourseId);
    $selectedInstructor = $instructors->firstWhere('id', $selectedInstructorId);
    $selectedCourseLabel = $selectedCourse
        ? $selectedCourse->title . ' - ' . ($selectedCourse->category->name ?? 'Chưa phân loại') . ' (#' . $selectedCourse->id . ')'
        : '';
    $selectedInstructorLabel = $selectedInstructor
        ? $selectedInstructor->fullname . ' - ' . $selectedInstructor->email
        : '';
    $days = ['2' => 'Thứ 2', '3' => 'Thứ 3', '4' => 'Thứ 4', '5' => 'Thứ 5', '6' => 'Thứ 6', '7' => 'Thứ 7', 'CN' => 'Chủ nhật'];
@endphp

<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-plus-circle me-2"></i>Thêm đợt học mới
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

        <form action="{{ route('admin.classes.store') }}" method="POST">
            @csrf

            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0">Thông tin đợt học</h6>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-bold">Tên đợt học</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Ví dụ: Khóa 01" required>
                                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="start_date" class="form-label fw-bold">Ngày bắt đầu</label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" id="start_date" name="start_date" value="{{ old('start_date') }}" required>
                                    @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="end_date" class="form-label fw-bold">Ngày kết thúc</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" id="end_date" name="end_date" value="{{ old('end_date') }}" required>
                                    @error('end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="schedule" class="form-label fw-bold">Lịch dự kiến</label>
                                    <textarea class="form-control @error('schedule') is-invalid @enderror" id="schedule" name="schedule" rows="2" placeholder="Ví dụ: Thứ 2, 4, 6 - 18:00 đến 20:00">{{ old('schedule') }}</textarea>
                                    @error('schedule')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="meeting_info" class="form-label fw-bold">Địa điểm / phòng học / link</label>
                                    <textarea class="form-control @error('meeting_info') is-invalid @enderror" id="meeting_info" name="meeting_info" rows="2" placeholder="Ví dụ: Phòng A2 hoặc Zoom link">{{ old('meeting_info') }}</textarea>
                                    @error('meeting_info')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="max_students" class="form-label fw-bold">Sức chứa</label>
                                    <input type="number" class="form-control @error('max_students') is-invalid @enderror" id="max_students" name="max_students" value="{{ old('max_students', 0) }}" min="0">
                                    <div class="form-text">0 = không giới hạn</div>
                                    @error('max_students')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-3">
                                    <label for="price_override" class="form-label fw-bold">Giá riêng</label>
                                    <input type="number" class="form-control @error('price_override') is-invalid @enderror" id="price_override" name="price_override" value="{{ old('price_override') }}" min="0" step="1000">
                                    @error('price_override')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0">Lịch chi tiết theo ca</h6>
                            <button type="button" class="btn btn-outline-success btn-sm add-slot" data-day="2">
                                <i class="fas fa-plus me-1"></i>Thêm ca
                            </button>
                        </div>
                        <div class="card-body">
                            @foreach($days as $key => $label)
                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <strong>{{ $label }}</strong>
                                        <button type="button" class="btn btn-outline-success btn-sm add-slot" data-day="{{ $key }}">Thêm ca</button>
                                    </div>
                                    <div class="slots-container" data-day="{{ $key }}">
                                        <div class="slot-row d-flex gap-2 mb-2">
                                            <input type="time" name="schedules[{{ $key }}][0][start]" class="form-control form-control-sm" value="{{ old('schedules.' . $key . '.0.start') }}">
                                            <input type="time" name="schedules[{{ $key }}][0][end]" class="form-control form-control-sm" value="{{ old('schedules.' . $key . '.0.end') }}">
                                            <button type="button" class="btn btn-danger btn-sm remove-slot">-</button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card mb-4">
                        <div class="card-header bg-light py-2">
                            <h6 class="card-title mb-0">Liên kết khóa học</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="course_picker" class="form-label fw-bold">Khóa học</label>
                                <input type="text" class="form-control @error('course_id') is-invalid @enderror" id="course_picker" list="course-options" value="{{ $selectedCourseLabel }}" placeholder="Gõ tên khóa học để tìm nhanh" autocomplete="off" required>
                                <input type="hidden" id="course_id" name="course_id" value="{{ $selectedCourseId }}">
                                <datalist id="course-options">
                                    @foreach($courses as $course)
                                        <option
                                            value="{{ $course->title }} - {{ $course->category->name ?? 'Chưa phân loại' }} (#{{ $course->id }})"
                                            data-id="{{ $course->id }}"
                                            data-title="{{ $course->title }}"
                                            data-category="{{ $course->category->name ?? 'Chưa phân loại' }}"
                                            data-status-label="{{ $course->status === 'published' ? 'Đã xuất bản' : 'Bản nháp' }}"
                                            data-edit-url="{{ route('admin.courses.edit', $course) }}"
                                            data-intakes-url="{{ route('admin.classes.index', ['course_id' => $course->id]) }}"
                                        ></option>
                                    @endforeach
                                </datalist>
                                <div class="form-text">Gõ tên khóa học để lọc nhanh, chọn từ danh sách gợi ý rồi tiếp tục tạo đợt học.</div>
                                @error('course_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div id="course-summary" class="alert alert-light border small {{ $selectedCourse ? '' : 'd-none' }}"></div>

                            <div class="mb-3">
                                <label for="instructor_picker" class="form-label fw-bold">Giảng viên</label>
                                <input type="text" class="form-control @error('instructor_id') is-invalid @enderror" id="instructor_picker" list="instructor-options" value="{{ $selectedInstructorLabel }}" placeholder="Gõ tên hoặc email giảng viên" autocomplete="off" required>
                                <input type="hidden" id="instructor_id" name="instructor_id" value="{{ $selectedInstructorId }}">
                                <datalist id="instructor-options">
                                    @foreach($instructors as $instructor)
                                        <option
                                            value="{{ $instructor->fullname }} - {{ $instructor->email }}"
                                            data-id="{{ $instructor->id }}"
                                            data-name="{{ $instructor->fullname }}"
                                            data-email="{{ $instructor->email }}"
                                            data-username="{{ $instructor->username }}"
                                        ></option>
                                    @endforeach
                                </datalist>
                                <div class="form-text">Bạn có thể gõ tên, rồi chọn giảng viên đúng từ gợi ý thay vì kéo danh sách dài.</div>
                                @error('instructor_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div id="instructor-summary" class="alert alert-light border small {{ $selectedInstructor ? '' : 'd-none' }}"></div>

                            <div>
                                <label for="status" class="form-label fw-bold">Trạng thái</label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Mở đăng ký</option>
                                    <option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                                </select>
                                @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Lưu đợt học
                </button>
            </div>
        </form>
    </div>
</div>

<template id="slot-template">
    <div class="slot-row d-flex gap-2 mb-2">
        <input type="time" name="__START__" class="form-control form-control-sm">
        <input type="time" name="__END__" class="form-control form-control-sm">
        <button type="button" class="btn btn-danger btn-sm remove-slot">-</button>
    </div>
</template>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bindLookup = function (config) {
        const input = document.getElementById(config.inputId);
        const hidden = document.getElementById(config.hiddenId);
        const dataList = document.getElementById(config.listId);
        const summary = document.getElementById(config.summaryId);
        const options = Array.from(dataList.querySelectorAll('option'));

        const findByValue = function (value) {
            return options.find(function (option) {
                return option.value.trim().toLowerCase() === value.trim().toLowerCase();
            });
        };

        const findById = function (id) {
            return options.find(function (option) {
                return String(option.dataset.id) === String(id);
            });
        };

        const renderSummary = function (option) {
            if (!summary) {
                return;
            }

            if (!option) {
                summary.classList.add('d-none');
                summary.innerHTML = '';
                return;
            }

            summary.classList.remove('d-none');
            summary.innerHTML = config.renderSummary(option);
        };

        const applyOption = function (option) {
            if (!option) {
                hidden.value = '';
                renderSummary(null);
                return false;
            }

            hidden.value = option.dataset.id || '';
            input.value = option.value;
            input.classList.remove('is-invalid');
            renderSummary(option);
            return true;
        };

        const syncFromInput = function () {
            const value = input.value.trim();
            if (!value) {
                hidden.value = '';
                input.classList.remove('is-invalid');
                renderSummary(null);
                return false;
            }

            const option = findByValue(value);
            if (!option) {
                hidden.value = '';
                renderSummary(null);
                return false;
            }

            return applyOption(option);
        };

        input.addEventListener('input', function () {
            if (input.value.trim() === '') {
                hidden.value = '';
                input.classList.remove('is-invalid');
                renderSummary(null);
                return;
            }

            syncFromInput();
        });

        input.addEventListener('change', function () {
            if (!syncFromInput()) {
                input.classList.add('is-invalid');
            }
        });

        input.addEventListener('blur', function () {
            if (input.value.trim() !== '' && !syncFromInput()) {
                input.classList.add('is-invalid');
            }
        });

        const initialOption = hidden.value ? findById(hidden.value) : findByValue(input.value || '');
        if (initialOption) {
            applyOption(initialOption);
        }
    };

    bindLookup({
        inputId: 'course_picker',
        hiddenId: 'course_id',
        listId: 'course-options',
        summaryId: 'course-summary',
        renderSummary: function (option) {
            return `
                <div class="fw-semibold mb-1">${option.dataset.title}</div>
                <div class="text-muted">Nhóm ngành: ${option.dataset.category}</div>
                <div class="text-muted">Trạng thái: ${option.dataset.statusLabel}</div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                    <a href="${option.dataset.editUrl}" class="btn btn-outline-primary btn-sm">Mở khóa học</a>
                    <a href="${option.dataset.intakesUrl}" class="btn btn-outline-secondary btn-sm">Xem đợt học</a>
                    <a href="{{ route('admin.classes.create') }}?course_id=${option.dataset.id}" class="btn btn-outline-success btn-sm">Tạo nhanh đợt mới</a>
                </div>
            `;
        },
    });

    bindLookup({
        inputId: 'instructor_picker',
        hiddenId: 'instructor_id',
        listId: 'instructor-options',
        summaryId: 'instructor-summary',
        renderSummary: function (option) {
            return `
                <div class="fw-semibold mb-1">${option.dataset.name}</div>
                <div class="text-muted">Email: ${option.dataset.email}</div>
                <div class="text-muted">Tài khoản: ${option.dataset.username}</div>
            `;
        },
    });

    document.addEventListener('click', function (event) {
        if (event.target.classList.contains('add-slot')) {
            const day = event.target.dataset.day;
            const container = document.querySelector(`.slots-container[data-day="${day}"]`);
            const index = container.querySelectorAll('.slot-row').length;
            const html = document.getElementById('slot-template').innerHTML
                .replace('__START__', `schedules[${day}][${index}][start]`)
                .replace('__END__', `schedules[${day}][${index}][end]`);
            container.insertAdjacentHTML('beforeend', html);
        }

        if (event.target.classList.contains('remove-slot')) {
            event.target.closest('.slot-row')?.remove();
        }
    });
});
</script>
@endsection