@extends('layouts.admin') 

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- Stats Grid -->
<div class="stats-grid mb-4">
    <div class="stat-card users">
        <div class="stat-icon">
            <i class="fas fa-users"></i>
        </div>
        <div class="stat-number">{{ $stats['total_users'] }}</div>
        <div class="stat-label">Tổng Users</div>
        <div class="stat-change positive">
            <i class="fas fa-arrow-up me-1"></i>{{ $stats['today_registrations'] }} hôm nay
        </div>
    </div>
    
    <div class="stat-card courses">
        <div class="stat-icon">
            <i class="fas fa-user-shield"></i>
        </div>
        <div class="stat-number">{{ $stats['total_admins'] }}</div>
        <div class="stat-label">Administrators</div>
    </div>
    
    <div class="stat-card revenue">
        <div class="stat-icon">
            <i class="fas fa-user-tie"></i>
        </div>
        <div class="stat-number">{{ $stats['total_staff'] }}</div>
        <div class="stat-label">Staff Members</div>
    </div>
    
    <div class="stat-card orders">
        <div class="stat-icon">
            <i class="fas fa-user-graduate"></i>
        </div>
        <div class="stat-number">{{ $stats['total_students'] }}</div>
        <div class="stat-label">Students</div>
    </div>
</div>

<div class="row">
    <!-- Recent Users -->
    <div class="col-md-8 mb-4">
        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">User mới đăng ký</h5>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-primary">Xem tất cả</a>
            </div>
            <div class="activity-list">
                @forelse($recentUsers as $user)
                <div class="activity-item">
                    <div class="activity-icon {{ $user->is_verified ? 'success' : 'warning' }}">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="activity-content">
                        <div class="activity-title">{{ $user->fullname }}</div>
                        <div class="activity-desc">
                            <span class="badge bg-secondary">{{ $user->role }}</span>
                            @if(!$user->is_verified)
                                <span class="badge bg-warning">Chưa xác thực</span>
                            @endif
                        </div>
                        <div class="activity-time">
                            {{ $user->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
                @empty
                <div class="text-center text-muted py-4">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <p>Không có user nào</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- System Status -->
    <div class="col-md-4 mb-4">
        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">Trạng thái hệ thống</h5>
            </div>
            <div class="status-list">
                <div class="status-item d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span>Users đã xác thực:</span>
                    <span class="badge bg-success">{{ $stats['verified_users'] }}</span>
                </div>
                <div class="status-item d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span>Chờ xác thực:</span>
                    <span class="badge bg-warning">{{ $stats['unverified_users'] }}</span>
                </div>
                <div class="status-item d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span>Đăng ký tuần này:</span>
                    <span class="badge bg-info">{{ $stats['weekly_registrations'] }}</span>
                </div>
                <div class="status-item d-flex justify-content-between align-items-center py-2">
                    <span>Tỷ lệ xác thực:</span>
                    <span class="badge bg-primary">
                        {{ $stats['total_users'] > 0 ? round(($stats['verified_users'] / $stats['total_users']) * 100, 1) : 0 }}%
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="chart-card mt-3">
            <div class="chart-header">
                <h5 class="chart-title">Hành động nhanh</h5>
            </div>
            <div class="quick-actions-grid">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                    <i class="fas fa-users me-2"></i>Quản lý Users
                </a>
                <a href="{{ route('admin.users.create') }}" class="btn btn-outline-success btn-sm w-100 mb-2">
                    <i class="fas fa-user-plus me-2"></i>Thêm User
                </a>
                <a href="{{ route('home') }}" class="btn btn-outline-info btn-sm w-100 mb-2">
                    <i class="fas fa-home me-2"></i>Về trang chủ
                </a>
            </div>
        </div>
    </div>
</div>

<!-- System Activity -->
<div class="row">
    <div class="col-12">
        <div class="chart-card">
            <div class="chart-header">
                <h5 class="chart-title">Phân bố Users theo Role</h5>
            </div>
            <div class="row text-center">
                <div class="col-md-3">
                    <div class="p-3">
                        <h3 class="text-primary">{{ $stats['total_admins'] }}</h3>
                        <p class="text-muted mb-0">Administrators</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3">
                        <h3 class="text-success">{{ $stats['total_staff'] }}</h3>
                        <p class="text-muted mb-0">Staff Members</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3">
                        <h3 class="text-info">{{ $stats['total_instructors'] }}</h3>
                        <p class="text-muted mb-0">Instructors</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="p-3">
                        <h3 class="text-warning">{{ $stats['total_students'] }}</h3>
                        <p class="text-muted mb-0">Students</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection