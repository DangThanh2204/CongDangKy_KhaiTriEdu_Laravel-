@extends('layouts.app')

@section('title', 'Thông tin cá nhân')

@section('content')
<section class="py-5 profile-section">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary">
                                <i class="fas fa-user-circle me-2"></i>Thông tin cá nhân
                            </h2>
                            <p class="text-muted">Quản lý thông tin tài khoản của bạn</p>
                        </div>

                        <div class="row">
                            <div class="col-md-4 text-center mb-4">
                                <div class="avatar-container">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->fullname }}" class="rounded-circle avatar-lg mb-3" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #e9ecef;">
                                    @else
                                        <div class="avatar-placeholder rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 3rem;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                    <h5 class="mb-1">{{ $user->fullname }}</h5>
                                    <p class="text-muted mb-0">{{ $user->username }}</p>
                                    <span class="badge bg-{{ $user->isAdmin() ? 'danger' : ($user->isStaff() ? 'warning' : 'info') }} mt-2">
                                        {{ $user->isAdmin() ? 'Admin' : ($user->isStaff() ? 'Staff' : 'Student') }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="profile-info">
                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">Họ và tên</label>
                                        <p class="mb-0 fs-5">{{ $user->fullname }}</p>
                                    </div>

                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">Tên đăng nhập</label>
                                        <p class="mb-0 fs-5">{{ $user->username }}</p>
                                    </div>

                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">Email</label>
                                        <p class="mb-0 fs-5">{{ $user->email }}</p>
                                    </div>

                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">Trạng thái</label>
                                        <p class="mb-0">
                                            <span class="badge bg-{{ $user->is_verified ? 'success' : 'warning' }}">
                                                <i class="fas fa-{{ $user->is_verified ? 'check-circle' : 'clock' }} me-1"></i>
                                                {{ $user->is_verified ? 'Đã xác thực' : 'Chưa xác thực' }}
                                            </span>
                                        </p>
                                    </div>

                                    <div class="info-item mb-3">
                                        <label class="form-label fw-semibold text-muted">Ngày tham gia</label>
                                        <p class="mb-0 fs-5">{{ $user->created_at->format('d/m/Y') }}</p>
                                    </div>

                                    @if($user->email_verified_at)
                                        <div class="info-item mb-3">
                                            <label class="form-label fw-semibold text-muted">Ngày xác thực email</label>
                                            <p class="mb-0 fs-5">{{ $user->email_verified_at->format('d/m/Y H:i') }}</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <a href="{{ route('profile.edit') }}" class="btn btn-primary">
                                        <i class="fas fa-edit me-2"></i>Chỉnh sửa thông tin
                                    </a>
                                    <a href="{{ route('profile.change-password.form') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-key me-2"></i>Đổi mật khẩu
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


@endsection