@extends('layouts.admin')

@section('title', 'Quản lý Users')
@section('page-title', 'Quản lý Users')

@section('content')
<!-- Header với thống kê -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Danh sách người dùng</h4>
                <p class="text-muted mb-0">Quản lý tất cả người dùng trên hệ thống</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Thêm người dùng
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid mb-4">
    <div class="stat-card users">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-number">{{ $stats['totalUsers'] ?? $users->total() }}</div>
        <div class="stat-label">Tổng người dùng</div>
    </div>
    
    <div class="stat-card courses">
        <div class="stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-number">{{ $stats['verifiedUsers'] ?? $users->where('is_verified', true)->count() }}</div>
        <div class="stat-label">Đã xác thực</div>
    </div>
    
    <div class="stat-card revenue">
        <div class="stat-icon">
            <i class="fas fa-user-clock"></i>
        </div>
        <div class="stat-number">{{ $stats['unverifiedUsers'] ?? $users->where('is_verified', false)->count() }}</div>
        <div class="stat-label">Chưa xác thực</div>
    </div>
    
    <div class="stat-card orders">
        <div class="stat-icon">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="stat-number">{{ $stats['adminUsers'] ?? $users->where('role', 'admin')->count() }}</div>
        <div class="stat-label">Quản trị viên</div>
    </div>
</div>

<!-- Filters và Search -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" placeholder="Tên, email hoặc username..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Vai trò</label>
                <select name="role" class="form-select">
                    <option value="">Tất cả vai trò</option>
                    <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Quản trị</option>
                    <option value="staff" {{ request('role') == 'staff' ? 'selected' : '' }}>Nhân viên</option>
                    <option value="instructor" {{ request('role') == 'instructor' ? 'selected' : '' }}>Giảng viên</option>
                    <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Học viên</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Đã xác thực</option>
                    <option value="unverified" {{ request('status') == 'unverified' ? 'selected' : '' }}>Chưa xác thực</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Lọc
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Users Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Người dùng</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Vai trò</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                @if($user->avatar)
                                    <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" 
                                         class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-1">{{ $user->fullname }}</h6>
                                    <small class="text-muted">ID: {{ $user->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>{{ $user->username }}</strong>
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>
                            <span class="badge 
                                @if($user->role == 'admin') bg-danger
                                @elseif($user->role == 'staff') bg-warning text-dark
                                @else bg-primary @endif">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td>
                            @if($user->is_verified)
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>Đã xác thực
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-clock me-1"></i>Chưa xác thực
                                </span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $user->created_at->format('d/m/Y') }}</small>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <!-- Edit Button -->
                                <a href="{{ route('admin.users.edit', $user) }}" 
                                   class="btn btn-outline-warning"
                                   title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Verify Button (only for unverified users) -->
                                @if(!$user->is_verified)
                                <form method="POST" action="{{ route('admin.users.verify', $user) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success" 
                                            title="Xác thực tài khoản">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                                
                                <!-- Toggle Status Button -->
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-info" 
                                            title="{{ $user->is_verified ? 'Vô hiệu hóa' : 'Kích hoạt' }}">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                
                                <!-- Delete Button (hide for current user) -->
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" 
                                            title="Xóa tài khoản">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @else
                                <button class="btn btn-outline-secondary" disabled title="Không thể xóa chính mình">
                                    <i class="fas fa-trash"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-users fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có người dùng nào</h5>
                            <p class="text-muted">Hãy thêm người dùng đầu tiên!</p>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Thêm người dùng
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($users->hasPages())
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted">
        Hiển thị {{ $users->firstItem() }} - {{ $users->lastItem() }} của {{ $users->total() }} người dùng
    </div>
    <div>
        <nav>
            <ul class="pagination mb-0">
                {{-- Previous Page Link --}}
                @if ($users->onFirstPage())
                    <li class="page-item disabled">
                        <span class="page-link">&laquo;</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $users->previousPageUrl() }}" rel="prev">&laquo;</a>
                    </li>
                @endif

                {{-- Pagination Elements --}}
                @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                    @if ($page == $users->currentPage())
                        <li class="page-item active">
                            <span class="page-link">{{ $page }}</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach

                {{-- Next Page Link --}}
                @if ($users->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $users->nextPageUrl() }}" rel="next">&raquo;</a>
                    </li>
                @else
                    <li class="page-item disabled">
                        <span class="page-link">&raquo;</span>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xác nhận xóa
    const deleteForms = document.querySelectorAll('form[method="DELETE"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const username = this.closest('tr').querySelector('strong').textContent;
            if (!confirm(`Bạn có chắc chắn muốn xóa user "${username}"? Hành động này không thể hoàn tác!`)) {
                e.preventDefault();
            }
        });
    });

    // Xác nhận xác thực
    const verifyForms = document.querySelectorAll('form[action*="verify"]');
    verifyForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const username = this.closest('tr').querySelector('strong').textContent;
            if (!confirm(`Bạn có chắc chắn muốn xác thực tài khoản "${username}"?`)) {
                e.preventDefault();
            }
        });
    });

    // Xác nhận thay đổi trạng thái
    const toggleForms = document.querySelectorAll('form[action*="toggle-status"]');
    toggleForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const username = this.closest('tr').querySelector('strong').textContent;
            const isVerified = this.closest('tr').querySelector('.badge.bg-success') !== null;
            const action = isVerified ? 'vô hiệu hóa' : 'kích hoạt';
            if (!confirm(`Bạn có chắc chắn muốn ${action} tài khoản "${username}"?`)) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endsection