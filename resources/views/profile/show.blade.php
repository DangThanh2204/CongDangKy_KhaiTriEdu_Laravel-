@extends('layouts.app')

@section('title', 'Thông tin cá nhân')
@section('page-class', 'page-profile-show')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            {{-- Hero with avatar --}}
            <div class="card border-0 shadow-sm student-hero-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center gap-4">
                        <div class="profile-avatar-wrap">
                            @if($user->avatar)
                                <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->fullname }}" class="profile-avatar-image">
                            @else
                                <div class="profile-avatar-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="text-white-50 small mb-1">Thông tin cá nhân</div>
                            <h2 class="text-white fw-bold mb-1">{{ $user->fullname }}</h2>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-light text-primary px-3 py-2">
                                    <i class="fas fa-user-tag me-1"></i>{{ $user->roleLabel() }}
                                </span>
                                @if($user->is_verified)
                                    <span class="badge bg-success-subtle text-success-emphasis px-3 py-2">
                                        <i class="fas fa-check-circle me-1"></i>Đã xác thực
                                    </span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2">
                                        <i class="fas fa-clock me-1"></i>Chưa xác thực
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('profile.edit') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-edit me-1"></i>Chỉnh sửa
                            </a>
                            <a href="{{ route('profile.change-password.form') }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-key me-1"></i>Đổi mật khẩu
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Info grid --}}
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="profile-info-icon bg-primary-subtle text-primary">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="text-muted small">Họ và tên</div>
                                    <div class="fw-bold text-truncate">{{ $user->fullname }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="profile-info-icon bg-info-subtle text-info">
                                    <i class="fas fa-at"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="text-muted small">Tên đăng nhập</div>
                                    <div class="fw-bold text-truncate">{{ $user->username }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="profile-info-icon bg-success-subtle text-success">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="text-muted small">Email</div>
                                    <div class="fw-bold text-truncate">{{ $user->email }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-3">
                                <div class="profile-info-icon bg-warning-subtle text-warning">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="text-muted small">Ngày tham gia</div>
                                    <div class="fw-bold text-truncate">{{ $user->created_at->format('d/m/Y') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @if($user->email_verified_at)
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="profile-info-icon bg-success-subtle text-success">
                                        <i class="fas fa-shield-check"></i>
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="text-muted small">Email đã được xác thực</div>
                                        <div class="fw-bold text-truncate">{{ $user->email_verified_at->format('d/m/Y H:i') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
