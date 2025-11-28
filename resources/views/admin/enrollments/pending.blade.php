@extends('layouts.admin')

@section('title', 'Yêu cầu đăng ký chờ duyệt')
@section('page-title', 'Yêu cầu đăng ký chờ duyệt')

@section('content')
<!-- Header với thống kê -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Yêu cầu đăng ký chờ duyệt</h4>
                <p class="text-muted mb-0">Quản lý các yêu cầu đăng ký khóa học đang chờ phê duyệt</p>
            </div>
            <a href="{{ route('admin.enrollments.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-list me-2"></i>Xem tất cả
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid mb-4">
    <div class="stat-card users">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-number">{{ $pendingCount }}</div>
        <div class="stat-label">Chờ duyệt</div>
    </div>
    
    <div class="stat-card courses">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-number">{{ $approvedCount }}</div>
        <div class="stat-label">Đã duyệt</div>
    </div>
    
    <div class="stat-card revenue">
        <div class="stat-icon">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-number">{{ $rejectedCount }}</div>
        <div class="stat-label">Đã từ chối</div>
    </div>
    
    <div class="stat-card orders">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-number">{{ $pendingCount + $approvedCount + $rejectedCount }}</div>
        <div class="stat-label">Tổng đăng ký</div>
    </div>
</div>

<!-- Enrollment Requests Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Học viên</th>
                        <th>Khóa học</th>
                        <th>Giảng viên</th>
                        <th>Ngày đăng ký</th>
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
                            <span class="fw-medium">{{ $enrollment->course->instructor->fullname }}</span>
                        </td>
                        <td>
                            <small class="text-muted">{{ $enrollment->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
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
                            </div>

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
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <i class="fas fa-check-circle fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Không có yêu cầu đăng ký nào chờ duyệt</h5>
                            <p class="text-muted">Tất cả các yêu cầu đã được xử lý!</p>
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
        Hiển thị {{ $enrollments->firstItem() }} - {{ $enrollments->lastItem() }} của {{ $enrollments->total() }} yêu cầu
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
});
</script>
@endsection