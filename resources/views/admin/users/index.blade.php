@extends('layouts.admin')

@section('title', 'Quản lý người dùng')
@section('page-title', 'Quản lý người dùng')
@section('page-class', 'page-admin-users')

@push('styles')
    @vite('resources/css/pages/admin/users.css')
@endpush

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-0">Danh sách người dùng</h4>
                    <p class="text-muted mb-0">Quản lý tài khoản, trạng thái xác thực và cấp học viên ngay trong một màn hình.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.users.export', request()->query(), false) }}" class="btn btn-outline-success">
                        <i class="fas fa-file-excel me-2"></i>Xuất Excel
                    </a>
                    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Thêm người dùng
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="stats-grid mb-4">
        <div class="stat-card users">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-number">{{ number_format($stats['total_users']) }}</div>
            <div class="stat-label">Tổng người dùng</div>
        </div>
        <div class="stat-card courses">
            <div class="stat-icon"><i class="fas fa-user-check"></i></div>
            <div class="stat-number">{{ number_format($stats['verified_users']) }}</div>
            <div class="stat-label">Đã xác thực</div>
        </div>
        <div class="stat-card revenue">
            <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
            <div class="stat-number">{{ number_format($stats['student_users']) }}</div>
            <div class="stat-label">Học viên</div>
        </div>
        <div class="stat-card orders">
            <div class="stat-icon"><i class="fas fa-user-shield"></i></div>
            <div class="stat-number">{{ number_format($stats['admin_users']) }}</div>
            <div class="stat-label">Quản trị viên</div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('admin.users.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Tìm kiếm</label>
                    <input type="text" name="search" class="form-control" placeholder="Tên, email hoặc username..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Vai trò</label>
                    <select name="role" class="form-select">
                        <option value="">Tất cả vai trò</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Quản trị</option>
                        <option value="staff" {{ request('role') === 'staff' ? 'selected' : '' }}>Nhân viên</option>
                        <option value="instructor" {{ request('role') === 'instructor' ? 'selected' : '' }}>Giảng viên</option>
                        <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Học viên</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Trạng thái</label>
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Đã xác thực</option>
                        <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Chưa xác thực</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Từ ngày</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Đến ngày</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button>
                </div>
                <div class="col-12">
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-rotate-right me-2"></i>Reset bộ lọc
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 admin-users-table">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Người dùng</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Cấp học viên</th>
                            <th>Trạng thái</th>
                            <th>Ngày tạo</th>
                            <th class="text-end pe-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            @php
                                $studentLevel = $user->student_level_summary;
                                $level = data_get($studentLevel, 'level');
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($user->avatar)
                                            <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="rounded-circle admin-user-avatar">
                                        @else
                                            <div class="admin-user-avatar admin-user-avatar-placeholder">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="mb-1">{{ $user->fullname }}</h6>
                                            <small class="text-muted">ID: {{ $user->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td><strong>{{ $user->username }}</strong></td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <span class="badge @if($user->role === 'admin') bg-danger @elseif($user->role === 'staff') bg-warning text-dark @elseif($user->role === 'instructor') bg-info text-dark @else bg-primary @endif">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td>
                                    @if($user->isStudent() && $level)
                                        <div class="admin-user-level level-{{ $level['key'] }}">
                                            <span class="admin-user-level-icon"><i class="{{ $level['icon'] }}"></i></span>
                                            <div>
                                                <strong>{{ $level['badge_name'] }}</strong>
                                                <small>{{ data_get($studentLevel, 'points_label', '0') }} điểm</small>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted small">Không áp dụng</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->is_verified)
                                        <span class="badge bg-success"><i class="fas fa-check me-1"></i>Đã xác thực</span>
                                    @else
                                        <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>Chưa xác thực</span>
                                    @endif
                                </td>
                                <td><small class="text-muted">{{ optional($user->created_at)->format('d/m/Y') }}</small></td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline-warning" title="Chỉnh sửa">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if(!$user->is_verified)
                                            <form method="POST" action="{{ route('admin.users.verify', $user) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-outline-success" title="Xác thực tài khoản">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-outline-info" title="{{ $user->is_verified ? 'Vô hiệu hóa' : 'Kích hoạt' }}">
                                                <i class="fas fa-power-off"></i>
                                            </button>
                                        </form>
                                        @if($user->id !== auth()->id())
                                            <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="d-inline delete-user-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Xóa tài khoản">
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
                                <td colspan="8" class="text-center py-5">
                                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Chưa có người dùng nào</h5>
                                    <p class="text-muted mb-4">Hãy thêm người dùng đầu tiên để bắt đầu quản lý hệ thống.</p>
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

    @if($users->hasPages())
        <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-2">
            <div class="text-muted">Hiển thị {{ $users->firstItem() }} - {{ $users->lastItem() }} của {{ $users->total() }} người dùng</div>
            <div>{{ $users->links() }}</div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.delete-user-form').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    const username = this.closest('tr').querySelector('strong').textContent;
                    if (!confirm(`Bạn có chắc muốn xóa tài khoản "${username}"? Hành động này không thể hoàn tác.`)) {
                        event.preventDefault();
                    }
                });
            });
        });
    </script>
@endpush