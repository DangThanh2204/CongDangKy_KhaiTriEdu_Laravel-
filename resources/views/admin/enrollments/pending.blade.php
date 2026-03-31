@extends('layouts.admin')

@section('title', 'Yêu cầu đăng ký offline chờ duyệt')
@section('page-title', 'Yêu cầu đăng ký offline chờ duyệt')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-0">Yêu cầu đăng ký offline chờ duyệt</h4>
                <p class="text-muted mb-0">Chỉ các khóa học offline mới vào hàng chờ duyệt. Khóa online sẽ được ghi danh tự động.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.enrollments.pending.export', request()->only('course_id'), false) }}" class="btn btn-outline-success">
                    <i class="fas fa-file-excel me-2"></i>Xuất CSV
                </a>
                <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-list me-2"></i>Xem tất cả đăng ký
                </a>
            </div>
        </div>
    </div>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card users">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-number">{{ $pendingCount }}</div>
        <div class="stat-label">Chờ duyệt</div>
    </div>
    <div class="stat-card courses">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-number">{{ $approvedCount }}</div>
        <div class="stat-label">Đã duyệt / hoàn thành</div>
    </div>
    <div class="stat-card revenue">
        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
        <div class="stat-number">{{ $rejectedCount }}</div>
        <div class="stat-label">Từ chối / đã hủy</div>
    </div>
    <div class="stat-card orders">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-number">{{ $pendingCount + $approvedCount + $rejectedCount }}</div>
        <div class="stat-label">Tổng đăng ký offline</div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-6 col-lg-4">
                <label for="course_id" class="form-label">Lọc theo khóa học offline</label>
                <select name="course_id" id="course_id" class="form-select">
                    <option value="">Tất cả khóa học offline</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ (string) request('course_id') === (string) $course->id ? 'selected' : '' }}>
                            {{ $course->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter me-2"></i>Lọc dữ liệu
                </button>
            </div>
            <div class="col-md-auto">
                <a href="{{ route('admin.enrollments.pending') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-rotate-left me-2"></i>Xóa lọc
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Học viên</th>
                        <th>Khóa học / đợt học</th>
                        <th>Giảng viên / lịch</th>
                        <th>Chỗ còn lại</th>
                        <th>Ngày đăng ký</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enrollment)
                        @php
                            $course = $enrollment->course;
                            $class = $enrollment->courseClass;
                            $remainingSlots = $class?->remaining_slots;
                            $isFull = $class?->is_full;
                            $instructorName = optional($class?->instructor)->fullname
                                ?? optional($class?->instructor)->username
                                ?? optional($course?->instructor)->fullname
                                ?? optional($course?->instructor)->username
                                ?? 'Chưa phân công';
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $enrollment->user->fullname ?? $enrollment->user->username }}</div>
                                        <div class="small text-muted">{{ $enrollment->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $course->title ?? 'Không xác định' }}</div>
                                <div class="small text-muted">Đợt học: {{ $class->name ?? 'Chưa gán đợt học' }}</div>
                                <div class="small text-muted">Nhóm ngành: {{ $course->category->name ?? 'Chưa phân loại' }}</div>
                            </td>
                            <td>
                                <div class="fw-medium">{{ $instructorName }}</div>
                                <div class="small text-muted">{{ $class?->schedule_text ?: 'Chưa cập nhật lịch học' }}</div>
                                <div class="small text-muted">{{ $class?->meeting_info ?: 'Chưa cập nhật phòng học / địa điểm' }}</div>
                            </td>
                            <td>
                                @if(is_null($remainingSlots))
                                    <span class="badge bg-light text-dark border">Không giới hạn</span>
                                @elseif($isFull)
                                    <span class="badge bg-danger">Đã đầy</span>
                                @else
                                    <span class="badge bg-light text-dark border">Còn {{ $remainingSlots }} chỗ</span>
                                @endif
                            </td>
                            <td>
                                <div class="small text-muted">{{ optional($enrollment->created_at)->format('d/m/Y H:i') }}</div>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <form method="POST" action="{{ route('admin.enrollments.approve', $enrollment) }}" class="d-inline approve-enrollment-form">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-outline-success" title="Duyệt đăng ký" {{ $isFull ? 'disabled' : '' }}>
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $enrollment->id }}" title="Từ chối đăng ký">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                @if($isFull)
                                    <div class="small text-danger mt-1">Đợt học đã đầy, không thể duyệt thêm.</div>
                                @endif

                                <div class="modal fade" id="rejectModal{{ $enrollment->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Từ chối đăng ký</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form method="POST" action="{{ route('admin.enrollments.reject', $enrollment) }}">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-body">
                                                    <p class="mb-2">Bạn đang từ chối đăng ký của <strong>{{ $enrollment->user->fullname ?? $enrollment->user->username }}</strong>.</p>
                                                    <div class="small text-muted mb-3">
                                                        <div>Khóa học: {{ $course->title ?? 'Không xác định' }}</div>
                                                        <div>Đợt học: {{ $class->name ?? 'Chưa gán đợt học' }}</div>
                                                    </div>
                                                    <div class="mb-0">
                                                        <label for="rejection_notes_{{ $enrollment->id }}" class="form-label">Lý do từ chối</label>
                                                        <textarea class="form-control" id="rejection_notes_{{ $enrollment->id }}" name="rejection_notes" rows="3" placeholder="Nhập lý do từ chối..." required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                                                    <button type="submit" class="btn btn-danger">Xác nhận từ chối</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Không có yêu cầu offline nào đang chờ duyệt</h5>
                                <p class="text-muted mb-0">Các yêu cầu mới sẽ xuất hiện ở đây khi học viên đăng ký khóa học offline.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($enrollments->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
        <div class="text-muted">
            Hiển thị {{ $enrollments->firstItem() }} - {{ $enrollments->lastItem() }} / {{ $enrollments->total() }} yêu cầu
        </div>
        <div>{{ $enrollments->links() }}</div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.approve-enrollment-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const row = this.closest('tr');
            const studentName = row?.querySelector('td:first-child .fw-semibold')?.textContent?.trim() || 'học viên';
            const courseName = row?.querySelector('td:nth-child(2) .fw-semibold')?.textContent?.trim() || 'khóa học';

            if (!confirm(`Duyệt đăng ký của "${studentName}" cho "${courseName}"?`)) {
                event.preventDefault();
            }
        });
    });
});
</script>
@endsection