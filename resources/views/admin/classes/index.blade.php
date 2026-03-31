@extends('layouts.admin')

@section('title', 'Quản lý đợt học')
@section('page-title', 'Quản lý đợt học')

@section('content')
@php
    $selectedCourse = $courses->firstWhere('id', request('course_id'));
    $selectedCourseLabel = $selectedCourse
        ? $selectedCourse->title . ' - ' . ($selectedCourse->category->name ?? 'Chưa phân loại') . ' (#' . $selectedCourse->id . ')'
        : '';
@endphp

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h4 class="mb-0">Danh sách đợt học</h4>
            <p class="text-muted mb-0">`classes` được hiển thị là các đợt mở đăng ký của từng khóa học.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.classes.export', request()->query(), false) }}" class="btn btn-outline-success">
                <i class="fas fa-file-excel me-2"></i>Xuất Excel
            </a>
            <a href="{{ route('admin.classes.create', request('course_id') ? ['course_id' => request('course_id')] : []) }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Thêm đợt học
            </a>
        </div>
    </div>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card users">
        <div class="stat-icon"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-number">{{ $stats['total'] }}</div>
        <div class="stat-label">Tổng đợt học</div>
    </div>
    <div class="stat-card courses">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-number">{{ $stats['active'] }}</div>
        <div class="stat-label">Mở đăng ký</div>
    </div>
    <div class="stat-card revenue">
        <div class="stat-icon"><i class="fas fa-pause-circle"></i></div>
        <div class="stat-number">{{ $stats['inactive'] }}</div>
        <div class="stat-label">Tạm dừng</div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Tên đợt học hoặc khóa học...">
            </div>
            <div class="col-md-4">
                <label for="course_picker" class="form-label">Khóa học</label>
                <input type="text" class="form-control" id="course_picker" list="course-options" value="{{ $selectedCourseLabel }}" placeholder="Gõ tên khóa học để lọc nhanh" autocomplete="off">
                <input type="hidden" id="course_id" name="course_id" value="{{ request('course_id') }}">
                <datalist id="course-options">
                    @foreach($courses as $course)
                        <option
                            value="{{ $course->title }} - {{ $course->category->name ?? 'Chưa phân loại' }} (#{{ $course->id }})"
                            data-id="{{ $course->id }}"
                            data-title="{{ $course->title }}"
                            data-category="{{ $course->category->name ?? 'Chưa phân loại' }}"
                            data-status-label="{{ $course->status === 'published' ? 'Đã xuất bản' : 'Bản nháp' }}"
                            data-edit-url="{{ route('admin.courses.edit', $course) }}"
                            data-create-intake-url="{{ route('admin.classes.create', ['course_id' => $course->id]) }}"
                        ></option>
                    @endforeach
                </datalist>
                <div class="form-text">Dễ quản lý hơn khi danh sách khóa học dài: chỉ cần gõ tên để lọc nhanh.</div>
            </div>
            <div class="col-md-2">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Mở đăng ký</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tạm dừng</option>
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label">Từ ngày</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>
            <div class="col-md-1">
                <label class="form-label">Đến ngày</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button>
            </div>
            <div class="col-12 d-flex flex-wrap gap-2">
                <a href="{{ route('admin.classes.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-rotate-right me-2"></i>Reset bộ lọc
                </a>
            </div>
        </form>

        <div id="selected-course-summary" class="alert alert-light border small mt-3 {{ $selectedCourse ? '' : 'd-none' }}"></div>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Đợt học</th>
                        <th>Khóa học</th>
                        <th>Giảng viên</th>
                        <th>Thời gian</th>
                        <th>Lịch dự kiến</th>
                        <th>Sức chứa</th>
                        <th>Đăng ký</th>
                        <th>Trạng thái</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($classes as $class)
                        <tr>
                            <td class="ps-4">
                                <div>
                                    <h6 class="mb-1">{{ $class->name }}</h6>
                                    <small class="text-muted">Mã #{{ $class->id }}</small>
                                </div>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $class->course->title ?? 'N/A' }}</div>
                                <small class="text-muted">{{ $class->course->category->name ?? 'Chưa phân loại' }}</small>
                            </td>
                            <td>{{ $class->instructor?->fullname ?? 'N/A' }}</td>
                            <td>{{ optional($class->start_date)->format('d/m/Y') }} - {{ optional($class->end_date)->format('d/m/Y') }}</td>
                            <td>{{ $class->schedule_text ?: 'Chưa cập nhật' }}</td>
                            <td>{{ $class->max_students > 0 ? $class->max_students : 'Không giới hạn' }}</td>
                            <td>{{ $class->enrollments_count }}</td>
                            <td>
                                @if($class->status === 'active')
                                    <span class="badge bg-success">Mở đăng ký</span>
                                @else
                                    <span class="badge bg-secondary">Tạm dừng</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.classes.show', $class) }}" class="btn btn-outline-secondary" title="Xem"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-outline-warning" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                    <form method="POST" action="{{ route('admin.classes.destroy', $class) }}" class="d-inline delete-class-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Xóa đợt học"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Chưa có đợt học nào</h5>
                                <p class="text-muted">Hãy tạo đợt học đầu tiên cho một khóa học.</p>
                                <a href="{{ route('admin.classes.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Tạo đợt học
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($classes->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">Hiển thị {{ $classes->firstItem() }} - {{ $classes->lastItem() }} của {{ $classes->total() }} đợt học</div>
        <div>{{ $classes->links() }}</div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const courseInput = document.getElementById('course_picker');
    const courseHidden = document.getElementById('course_id');
    const courseOptions = Array.from(document.querySelectorAll('#course-options option'));
    const courseSummary = document.getElementById('selected-course-summary');

    const findCourseByValue = function (value) {
        return courseOptions.find(function (option) {
            return option.value.trim().toLowerCase() === value.trim().toLowerCase();
        });
    };

    const findCourseById = function (id) {
        return courseOptions.find(function (option) {
            return String(option.dataset.id) === String(id);
        });
    };

    const renderCourseSummary = function (option) {
        if (!option) {
            courseSummary.classList.add('d-none');
            courseSummary.innerHTML = '';
            return;
        }

        courseSummary.classList.remove('d-none');
        courseSummary.innerHTML = `
            <div class="fw-semibold mb-1">${option.dataset.title}</div>
            <div class="text-muted">Nhóm ngành: ${option.dataset.category}</div>
            <div class="text-muted">Trạng thái khóa học: ${option.dataset.statusLabel}</div>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <a href="${option.dataset.editUrl}" class="btn btn-outline-primary btn-sm">Mở khóa học</a>
                <a href="${option.dataset.createIntakeUrl}" class="btn btn-outline-success btn-sm">Tạo đợt học cho khóa này</a>
            </div>
        `;
    };

    const syncCourse = function () {
        const value = courseInput.value.trim();
        if (!value) {
            courseHidden.value = '';
            courseInput.classList.remove('is-invalid');
            renderCourseSummary(null);
            return false;
        }

        const option = findCourseByValue(value);
        if (!option) {
            courseHidden.value = '';
            renderCourseSummary(null);
            return false;
        }

        courseHidden.value = option.dataset.id;
        courseInput.value = option.value;
        courseInput.classList.remove('is-invalid');
        renderCourseSummary(option);
        return true;
    };

    courseInput.addEventListener('input', function () {
        if (courseInput.value.trim() === '') {
            courseHidden.value = '';
            courseInput.classList.remove('is-invalid');
            renderCourseSummary(null);
            return;
        }

        syncCourse();
    });

    courseInput.addEventListener('change', function () {
        if (!syncCourse()) {
            courseInput.classList.add('is-invalid');
        }
    });

    courseInput.addEventListener('blur', function () {
        if (courseInput.value.trim() !== '' && !syncCourse()) {
            courseInput.classList.add('is-invalid');
        }
    });

    const initialCourse = courseHidden.value ? findCourseById(courseHidden.value) : findCourseByValue(courseInput.value || '');
    if (initialCourse) {
        courseHidden.value = initialCourse.dataset.id;
        courseInput.value = initialCourse.value;
        renderCourseSummary(initialCourse);
    }

    document.querySelectorAll('.delete-class-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const className = this.closest('tr').querySelector('h6')?.textContent || 'đợt học';
            if (!confirm(`Bạn có chắc muốn xóa đợt học "${className}"?`)) {
                event.preventDefault();
            }
        });
    });
});
</script>
@endsection