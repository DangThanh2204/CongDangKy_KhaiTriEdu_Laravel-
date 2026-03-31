@extends('layouts.admin')

@section('title', 'Quản lý Giảng viên')
@section('page-title', 'Quản lý Giảng viên')

@section('content')
<!-- Header với thống kê -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Danh sách giảng viên</h4>
                <p class="text-muted mb-0">Quản lý tất cả giảng viên trên hệ thống</p>
            </div>
            <a href="{{ route('admin.teachers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Thêm giảng viên
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid mb-4">
    <div class="stat-card users">
        <div class="stat-icon">
            <i class="fas fa-chalkboard-teacher"></i>
        </div>
        <div class="stat-number">{{ $stats['totalTeachers'] ?? $teachers->total() }}</div>
        <div class="stat-label">Tổng giảng viên</div>
    </div>
    
    <div class="stat-card courses">
        <div class="stat-icon">
            <i class="fas fa-user-check"></i>
        </div>
        <div class="stat-number">{{ $stats['verifiedTeachers'] ?? $teachers->where('is_verified', true)->count() }}</div>
        <div class="stat-label">Đã xác thực</div>
    </div>
    
    <div class="stat-card revenue">
        <div class="stat-icon">
            <i class="fas fa-user-clock"></i>
        </div>
        <div class="stat-number">{{ $stats['unverifiedTeachers'] ?? $teachers->where('is_verified', false)->count() }}</div>
        <div class="stat-label">Chưa xác thực</div>
    </div>
</div>

<!-- Filters và Search -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.teachers.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" placeholder="Tên, email hoặc username..." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Đã xác thực</option>
                    <option value="unverified" {{ request('status') == 'unverified' ? 'selected' : '' }}>Chưa xác thực</option>
                </select>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Lọc
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Teachers Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Giảng viên</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($teachers as $teacher)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                @if($teacher->avatar)
                                    <img src="{{ Storage::url($teacher->avatar) }}" alt="Avatar" 
                                         class="rounded-circle me-3" width="40" height="40" style="object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-1">{{ $teacher->fullname }}</h6>
                                    <small class="text-muted">ID: {{ $teacher->id }}</small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>{{ $teacher->username }}</strong>
                        </td>
                        <td>{{ $teacher->email }}</td>
                        <td>
                            @if($teacher->is_verified)
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
                            <small class="text-muted">{{ $teacher->created_at->format('d/m/Y') }}</small>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.teachers.edit', $teacher) }}" 
                                   class="btn btn-outline-warning"
                                   title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                @if(!$teacher->is_verified)
                                <form method="POST" action="{{ route('admin.users.verify', $teacher) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-success" 
                                            title="Xác thực tài khoản">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>
                                @endif
                                
                                <form method="POST" action="{{ route('admin.users.toggle-status', $teacher) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-info" 
                                            title="{{ $teacher->is_verified ? 'Vô hiệu hóa' : 'Kích hoạt' }}">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                
                                @if($teacher->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.teachers.destroy', $teacher) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" 
                                            title="Xóa giảng viên">
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
                        <td colspan="6" class="text-center py-4">
                            Không có giảng viên nào.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer">
        {{ $teachers->links() }}
    </div>
</div>
@endsection
