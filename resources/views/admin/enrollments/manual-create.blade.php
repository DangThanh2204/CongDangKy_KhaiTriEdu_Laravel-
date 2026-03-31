@extends('layouts.admin')

@section('title', 'Thêm học viên vào đợt học')
@section('page-title', 'Thêm học viên vào đợt học')

@section('content')
@php
    $courseOptions = $courses->mapWithKeys(function ($course) {
        return [
            $course->id => [
                'title' => $course->title,
                'delivery_mode' => $course->delivery_mode,
                'delivery_mode_label' => $course->delivery_mode_label,
                'classes' => $course->classes->map(function ($class) {
                    return [
                        'id' => $class->id,
                        'name' => $class->name,
                        'start_date' => optional($class->start_date)->format('d/m/Y'),
                        'end_date' => optional($class->end_date)->format('d/m/Y'),
                        'status' => $class->status,
                        'status_text' => $class->status_text,
                        'meeting_info' => $class->meeting_info,
                    ];
                })->values()->all(),
            ],
        ];
    });
@endphp

<div class="row justify-content-center">
    <div class="col-xl-8 col-lg-9">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-plus me-2"></i>Thêm học viên vào đợt học
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.enrollments.manual-enroll') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="course_id" class="form-label">Chọn khóa học <span class="text-danger">*</span></label>
                        <select name="course_id" id="course_id" class="form-select" required>
                            <option value="">-- Chọn khóa học --</option>
                            @foreach($courses as $course)
                                <option
                                    value="{{ $course->id }}"
                                    data-mode="{{ $course->delivery_mode }}"
                                    {{ old('course_id') == $course->id ? 'selected' : '' }}
                                >
                                    {{ $course->title }} - {{ $course->delivery_mode_label }} - {{ $course->category->name ?? 'Chưa có nhóm ngành' }}
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Khóa học là chương trình đào tạo. Học viên vẫn được xếp vào một đợt học cụ thể để quản lý lịch và số lượng.</div>
                        @error('course_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-light border d-flex justify-content-between align-items-start flex-wrap gap-3" id="deliveryModeNotice">
                        <div>
                            <div class="fw-semibold" id="deliveryModeTitle">Chưa chọn khóa học</div>
                            <div class="small text-muted mb-0" id="deliveryModeText">Chọn khóa học để hệ thống hướng dẫn đúng quy trình online hoặc offline.</div>
                        </div>
                        <span class="badge bg-secondary" id="deliveryModeBadge">Chưa xác định</span>
                    </div>

                    <div class="mb-3">
                        <label for="class_id" class="form-label">Chọn đợt học <span class="text-danger">*</span></label>
                        <select name="class_id" id="class_id" class="form-select" required disabled>
                            <option value="">-- Chọn khóa học trước --</option>
                        </select>
                        <div id="classHelp" class="form-text">Mỗi khóa học có thể có nhiều đợt học như Khóa 01, Khóa 02, Khóa 03.</div>
                        @error('class_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="fullname" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control"
                                id="fullname"
                                name="fullname"
                                value="{{ old('fullname') }}"
                                required
                                placeholder="Nhập họ và tên đầy đủ"
                            >
                            @error('fullname')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                            <input
                                type="email"
                                class="form-control"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                placeholder="Nhập email"
                            >
                            @error('email')
                                <div class="text-danger small">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-3">
                        <label for="notes" class="form-label">Ghi chú</label>
                        <textarea
                            class="form-control"
                            id="notes"
                            name="notes"
                            rows="2"
                            placeholder="Ghi chú thêm nếu cần"
                        >{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_approve" name="auto_approve" value="1" {{ old('auto_approve') ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto_approve">
                                <strong id="autoApproveTitle">Duyệt ngay sau khi tạo</strong>
                                <small class="d-block text-muted" id="autoApproveText">Khóa online sẽ được duyệt tự động. Với khóa offline, bạn có thể để pending hoặc duyệt ngay nếu hồ sơ đã hoàn tất.</small>
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4 flex-wrap gap-2">
                        <a href="{{ route('admin.enrollments.pending') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Thêm học viên
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-info-circle me-2 text-primary"></i>Gợi ý nghiệp vụ</h6>
                <ul class="small text-muted mb-0 ps-3">
                    <li>Khóa online được ghi danh tự động ở trạng thái approved sau khi đăng ký hợp lệ.</li>
                    <li>Khóa offline thường nên để pending để admin duyệt sau, đặc biệt khi cần kiểm tra hồ sơ và số chỗ.</li>
                    <li>Đợt học dùng để quản lý khai giảng, lịch học, giáo viên, phòng học và sức chứa.</li>
                    <li>Nếu email chưa tồn tại, hệ thống sẽ tự tạo tài khoản học viên mới.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const courseOptions = @json($courseOptions);
    const courseSelect = document.getElementById('course_id');
    const classSelect = document.getElementById('class_id');
    const classHelp = document.getElementById('classHelp');
    const deliveryModeTitle = document.getElementById('deliveryModeTitle');
    const deliveryModeText = document.getElementById('deliveryModeText');
    const deliveryModeBadge = document.getElementById('deliveryModeBadge');
    const autoApproveCheckbox = document.getElementById('auto_approve');
    const autoApproveTitle = document.getElementById('autoApproveTitle');
    const autoApproveText = document.getElementById('autoApproveText');
    const oldClassId = '{{ old('class_id') }}';
    const hasOldAutoApprove = @json(old('auto_approve') !== null);

    function renderClasses(courseId, selectedClassId) {
        const courseMeta = courseOptions[courseId] || null;
        const classes = courseMeta?.classes || [];

        classSelect.innerHTML = '';

        if (!courseId) {
            classSelect.disabled = true;
            classSelect.innerHTML = '<option value="">-- Chọn khóa học trước --</option>';
            classHelp.textContent = 'Mỗi khóa học có thể có nhiều đợt học như Khóa 01, Khóa 02, Khóa 03.';
            return;
        }

        if (!classes.length) {
            classSelect.disabled = true;
            classSelect.innerHTML = '<option value="">-- Khóa học này chưa có đợt học --</option>';
            classHelp.textContent = 'Vui lòng tạo ít nhất một đợt học trước khi thêm học viên vào khóa học này.';
            return;
        }

        classSelect.disabled = false;
        classSelect.innerHTML = '<option value="">-- Chọn đợt học --</option>';
        classHelp.textContent = 'Chọn đúng đợt học mà học viên sẽ tham gia.';

        classes.forEach(function (item) {
            const option = document.createElement('option');
            const scheduleText = item.start_date && item.end_date
                ? ` (${item.start_date} - ${item.end_date})`
                : '';

            option.value = item.id;
            option.textContent = `${item.name}${scheduleText}`;

            if (String(selectedClassId) === String(item.id)) {
                option.selected = true;
            }

            classSelect.appendChild(option);
        });
    }

    function syncModeUi() {
        const courseId = courseSelect.value;
        const courseMeta = courseOptions[courseId] || null;
        const mode = courseMeta?.delivery_mode || null;

        if (!mode) {
            deliveryModeTitle.textContent = 'Chưa chọn khóa học';
            deliveryModeText.textContent = 'Chọn khóa học để hệ thống hướng dẫn đúng quy trình online hoặc offline.';
            deliveryModeBadge.textContent = 'Chưa xác định';
            deliveryModeBadge.className = 'badge bg-secondary';
            autoApproveCheckbox.disabled = false;
            autoApproveTitle.textContent = 'Duyệt ngay sau khi tạo';
            autoApproveText.textContent = 'Khóa online sẽ được duyệt tự động. Với khóa offline, bạn có thể để pending hoặc duyệt ngay nếu hồ sơ đã hoàn tất.';
            return;
        }

        if (mode === 'online') {
            deliveryModeTitle.textContent = 'Khóa học online';
            deliveryModeText.textContent = 'Học viên được ghi danh tự động ở trạng thái approved và có thể vào học ngay sau khi tạo đăng ký hợp lệ.';
            deliveryModeBadge.textContent = 'Online';
            deliveryModeBadge.className = 'badge bg-success';
            autoApproveCheckbox.checked = true;
            autoApproveCheckbox.disabled = true;
            autoApproveTitle.textContent = 'Khóa online sẽ tự động duyệt';
            autoApproveText.textContent = 'Bạn không cần thao tác duyệt tay. Hệ thống sẽ tạo enrollment approved ngay.';
            return;
        }

        deliveryModeTitle.textContent = 'Khóa học offline';
        deliveryModeText.textContent = 'Học viên đăng ký offline sẽ vào trạng thái pending. Admin có thể duyệt hoặc từ chối sau khi kiểm tra hồ sơ và số chỗ.';
        deliveryModeBadge.textContent = 'Offline';
        deliveryModeBadge.className = 'badge bg-primary';
        autoApproveCheckbox.disabled = false;
        if (!hasOldAutoApprove) {
            autoApproveCheckbox.checked = false;
        }
        autoApproveTitle.textContent = 'Duyệt ngay thay vì để pending';
        autoApproveText.textContent = 'Giữ tắt nếu bạn muốn đăng ký vào hàng chờ duyệt. Bật lên khi admin đã chốt hồ sơ và muốn approved ngay.';
    }

    courseSelect.addEventListener('change', function () {
        renderClasses(this.value, '');
        syncModeUi();
    });

    renderClasses(courseSelect.value, oldClassId);
    syncModeUi();
});
</script>
@endsection