@extends('layouts.admin')

@section('title', 'Quản lý Đăng ký')
@section('page-title', 'Quản lý Đăng ký')

@section('content')
<!-- Header -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Quản lý đăng ký khóa học</h4>
                <p class="text-muted mb-0">Tất cả đăng ký khóa học trên hệ thống</p>
            </div>
            <div class="btn-group">
                <a href="{{ route('admin.enrollments.manual-create') }}" class="btn btn-success">
                    <i class="fas fa-user-plus me-2"></i>Thêm học viên
                </a>
                <a href="{{ route('admin.enrollments.pending') }}" class="btn btn-primary">
                    <i class="fas fa-clock me-2"></i>Yêu cầu chờ duyệt
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Chờ duyệt</option>
                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Đã duyệt</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Đã từ chối</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Khóa học</label>
                <select name="course_id" class="form-select">
                    <option value="">Tất cả khóa học</option>
                    @foreach($courses as $course)
                    <option value="{{ $course->id }}" {{ request('course_id') == $course->id ? 'selected' : '' }}>
                        {{ $course->title }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Lọc
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-refresh me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Enrollments Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
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
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-user text-primary fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $enrollment->user->fullname }}</h6>
                                    <small class="text-muted">{{ $enrollment->user->email }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <h6 class="mb-1">{{ $enrollment->course->title }}</h6>
                            <span class="badge bg-secondary">{{ $enrollment->course->category->name ?? 'Chưa phân loại' }}</span>
                        </td>
                        <td>
                            @if($enrollment->status === 'pending')
                                <span class="badge bg-warning">
                                    <i class="fas fa-clock me-1"></i>Chờ duyệt
                                </span>
                            @elseif($enrollment->status === 'approved')
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>Đã duyệt
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    <i class="fas fa-times me-1"></i>Đã từ chối
                                </span>
                                @if($enrollment->notes)
                                <small class="d-block text-muted mt-1" title="{{ $enrollment->notes }}">
                                    <i class="fas fa-comment me-1"></i>{{ Str::limit($enrollment->notes, 30) }}
                                </small>
                                @endif
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $enrollment->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            @if($enrollment->approved_at)
                                <small class="text-muted">{{ $enrollment->approved_at->format('d/m/Y H:i') }}</small>
                            @elseif($enrollment->rejected_at)
                                <small class="text-muted">{{ $enrollment->rejected_at->format('d/m/Y H:i') }}</small>
                            @else
                                <small class="text-muted">--</small>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                @if($enrollment->isPending())
                                <!-- Approve Button -->
                                <form method="POST" action="{{ route('admin.enrollments.approve', $enrollment) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success" 
                                            title="Duyệt đăng ký">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>

                                <!-- Reject Button -->
                                <button type="button" class="btn btn-outline-danger" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#rejectModal{{ $enrollment->id }}"
                                        title="Từ chối đăng ký">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                                
                                <!-- Delete Button -->
                                <form method="POST" action="{{ route('admin.enrollments.destroy', $enrollment) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" 
                                            title="Xóa đăng ký">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>

                            @if($enrollment->isPending())
                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $enrollment->id }}" tabindex="-1">
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
                                                <p>Bạn đang từ chối đăng ký của <strong>{{ $enrollment->user->fullname }}</strong> cho khóa học <strong>{{ $enrollment->course->title }}</strong>.</p>
                                                
                                                <div class="mb-3">
                                                    <label for="rejection_notes" class="form-label">Lý do từ chối</label>
                                                    <textarea class="form-control" id="rejection_notes" 
                                                              name="rejection_notes" rows="3" 
                                                              placeholder="Nhập lý do từ chối..." required></textarea>
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
                            <p class="text-muted">Chưa có học viên nào đăng ký khóa học!</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($enrollments->hasPages())
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted">
        Hiển thị {{ $enrollments->firstItem() }} - {{ $enrollments->lastItem() }} của {{ $enrollments->total() }} đăng ký
    </div>
    <div>
        {{ $enrollments->links() }}
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xác nhận duyệt
    const approveForms = document.querySelectorAll('form[action*="approve"]');
    approveForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const userName = this.closest('tr').querySelector('h6').textContent;
            const courseName = this.closest('tr').querySelectorAll('h6')[1].textContent;
            if (!confirm(`Bạn có chắc chắn muốn duyệt đăng ký của "${userName}" cho khóa học "${courseName}"?`)) {
                e.preventDefault();
            }
        });
    });

    // Xác nhận xóa
    const deleteForms = document.querySelectorAll('form[method="DELETE"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const userName = this.closest('tr').querySelector('h6').textContent;
            const courseName = this.closest('tr').querySelectorAll('h6')[1].textContent;
            if (!confirm(`Bạn có chắc chắn muốn xóa đăng ký của "${userName}" cho khóa học "${courseName}"?`)) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endsection