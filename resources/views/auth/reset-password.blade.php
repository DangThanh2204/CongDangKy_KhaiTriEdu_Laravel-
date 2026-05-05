@extends('layouts.app')

@section('title', 'Đặt lại mật khẩu')

@section('content')
<section class="py-5 profile-section">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-danger">
                                <i class="fas fa-key me-2"></i>Đặt lại mật khẩu
                            </h2>
                            <p class="text-muted">Nhập mã OTP bạn đã nhận và mật khẩu mới.</p>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.reset') }}">
                            @csrf
                            <input type="hidden" name="username" value="{{ $username }}">
                            <input type="hidden" name="email" value="{{ $email }}">

                            <div class="mb-3">
                                <label for="otp" class="form-label fw-semibold">Mã OTP <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="otp" name="otp" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Mật khẩu mới <span class="text-danger">*</span></label>
                                <input type="password" class="form-control form-control-lg" id="password" name="password" required>
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label fw-semibold">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                                <input type="password" class="form-control form-control-lg" id="password_confirmation" name="password_confirmation" required>
                            </div>

                            <button type="submit" class="btn btn-danger btn-lg w-100">
                                <i class="fas fa-sync-alt me-2"></i>Đặt lại mật khẩu
                            </button>

                            <div class="mt-3 text-center">
                                <a href="{{ route('login') }}">Quay lại đăng nhập</a>
                                <br>
                                <small>Nếu không nhận được mã OTP, <a href="{{ route('password.forgot') }}">bấm vào đây</a> để gửi lại.</small>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
