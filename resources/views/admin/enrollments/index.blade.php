@extends('layouts.admin')

@section('title', 'Quản lý đăng ký')
@section('page-title', 'Quản lý đăng ký')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-0">Quản lý đăng ký khóa học</h4>
                <p class="text-muted mb-0">Theo dõi đăng ký online/offline và xử lý yêu cầu đăng ký offline.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.enrollments.export', request()->query(), false) }}" class="btn btn-outline-success">
                    <i class="fas fa-file-excel me-2"></i>Xuất Excel
                </a>
                <a href="{{ route('admin.enrollments.manual-create') }}" class="btn btn-success">
                    <i class="fas fa-user-plus me-2"></i>Thêm học viên
                </a>
                <a href="{{ route('admin.enrollments.pending') }}" class="btn btn-primary">
                    <i class="fas fa-clock me-2"></i>Yêu cầu offline chờ duyệt
                </a>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Từ chối</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Hình thức đào tạo</label>
                <select name="delivery_mode" class="form-select">
                    <option value="">Tất cả hình thức</option>
                    <option value="online" {{ request('delivery_mode') === 'online' ? 'selected' : '' }}>Online</option>
                    <option value="offline" {{ request('delivery_mode') === 'offline' ? 'selected' : '' }}>Offline</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Khóa học</label>
                <select name="course_id" class="form-select">
                    <option value="">Tất cả khóa học</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ (string) request('course_id') === (string) $course->id ? 'selected' : '' }}>
                            {{ $course->title }} ({{ $course->delivery_mode_label }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button>
            </div>
            <div class="col-md-1 d-grid">
                <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-secondary"><i class="fas fa-rotate-right"></i></a>
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
                        <th>Khóa học</th>
                        <th>Trạng thái</th>
                        <th>Ngày đăng ký</th>
                        <th>Ngày xử lý</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enrollment)
                        @php
                            $processedAt = $enrollment->approved_at ?? $enrollment->rejected_at ?? $enrollment->cancelled_at ?? $enrollment->completed_at;
                            $remainingSlots = $enrollment->courseClass?->remaining_slots;
                            $isOffline = $enrollment->delivery_mode === 'offline';
                        @endphp
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="me-3"><i class="fas fa-user text-primary fs-5"></i></div>
                                    <div>
                                        <h6 class="mb-1">{{ $enrollment->user->fullname ?? $enrollment->user->username }}</h6>
                                        <small class="text-muted">{{ $enrollment->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <h6 class="mb-1">{{ $enrollment->course->title ?? 'Khóa học đã xóa' }}</h6>
                                <div class="d-flex flex-wrap gap-2 small mb-1">
                                    <span class="badge bg-{{ $isOffline ? 'dark' : 'info text-dark' }}">{{ $enrollment->course?->delivery_mode_label ?? 'Online' }}</span>
                                    <span class="badge bg-secondary">{{ $enrollment->course->category->name ?? 'Chưa phân loại' }}</span>
                                </div>
                                <div class="small text-muted">Đợt học: {{ $enrollment->courseClass->name ?? 'Chưa gán đợt học' }}</div>
                                @if($isOffline)
                                    <div class="small text-muted">Chỗ còn lại: {{ is_null($remainingSlots) ? 'Không giới hạn' : $remainingSlots }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $enrollment->status_color }}{{ $enrollment->status_color === 'warning' ? ' text-dark' : '' }}">{{ $enrollment->status_text }}</span>
                                @if($enrollment->notes)
                                    <small class="d-block text-muted mt-2" title="{{ $enrollment->notes }}">
                                        <i class="fas fa-note-sticky me-1"></i>{{ \Illuminate\Support\Str::limit($enrollment->notes, 40) }}
                                    </small>
                                @endif
                            </td>
                            <td><small class="text-muted">{{ optional($enrollment->created_at)->format('d/m/Y H:i') }}</small></td>
                            <td><small class="text-muted">{{ optional($processedAt)->format('d/m/Y H:i') ?: '--' }}</small></td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    @if($enrollment->isPending())
                                        <form method="POST" action="{{ route('admin.enrollments.approve', $enrollment) }}" class="d-inline approve-enrollment-form">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-success" title="Duyệt đăng ký"><i class="fas fa-check"></i></button>
                                        </form>
                                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $enrollment->id }}" title="Từ chối đăng ký"><i class="fas fa-times"></i></button>
                                    @endif
                                    <form method="POST" action="{{ route('admin.enrollments.destroy', $enrollment) }}" class="d-inline delete-enrollment-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" title="Xóa đăng ký"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>

                                @if($enrollment->isPending())
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
                                                        <p>Bạn đang từ chối đăng ký của <strong>{{ $enrollment->user->fullname ?? $enrollment->user->username }}</strong> cho khóa học <strong>{{ $enrollment->course->title ?? 'Khóa học' }}</strong>.</p>
                                                        <p class="small text-muted mb-3">Đợt học: {{ $enrollment->courseClass->name ?? 'Chưa gán đợt học' }}</p>
                                                        <div class="mb-3">
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
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Không có đăng ký nào</h5>
                                <p class="text-muted mb-0">Chưa có học viên nào đăng ký theo bộ lọc hiện tại.</p>
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
        <div class="text-muted">Hiển thị {{ $enrollments->firstItem() }} - {{ $enrollments->lastItem() }} của {{ $enrollments->total() }} đăng ký</div>
        <div>{{ $enrollments->links() }}</div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.approve-enrollment-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const row = this.closest('tr');
            const headings = row.querySelectorAll('h6');
            const userName = headings[0]?.textContent?.trim() || 'học viên';
            const courseName = headings[1]?.textContent?.trim() || 'khóa học';
            if (!confirm(`Bạn có chắc muốn duyệt đăng ký của "${userName}" cho khóa học "${courseName}"?`)) {
                event.preventDefault();
            }
        });
    });

    document.querySelectorAll('.delete-enrollment-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const row = this.closest('tr');
            const headings = row.querySelectorAll('h6');
            const userName = headings[0]?.textContent?.trim() || 'học viên';
            const courseName = headings[1]?.textContent?.trim() || 'khóa học';
            if (!confirm(`Bạn có chắc muốn xóa đăng ký của "${userName}" cho khóa học "${courseName}"?`)) {
                event.preventDefault();
            }
        });
    });
});
</script>
@endsection