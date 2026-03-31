@extends('layouts.app')

@section('title', 'Đổi mật khẩu')

@section('content')
<section class="py-5 profile-section">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-danger">
                                <i class="fas fa-key me-2"></i>Đổi mật khẩu
                            </h2>
                            <p class="text-muted">Nhập mật khẩu hiện tại và mật khẩu mới của bạn</p>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span class="fw-semibold">Có lỗi xảy ra!</span>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('profile.change-password') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="current_password" class="form-label fw-semibold">
                                    Mật khẩu hiện tại <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control form-control-lg" id="current_password" name="current_password" required>
                                @error('current_password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">
                                    Mật khẩu mới <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                                @error('password')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label fw-semibold">
                                    Xác nhận mật khẩu mới <span class="text-danger">*</span>
                                </label>
                                <input type="password" class="form-control form-control-lg" id="password_confirmation" name="password_confirmation" required>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="{{ route('profile.edit') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Quay lại chỉnh sửa
                                </a>
                                <button type="submit" class="btn btn-danger btn-lg">
                                    <i class="fas fa-sync-alt me-2"></i>Đổi mật khẩu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
