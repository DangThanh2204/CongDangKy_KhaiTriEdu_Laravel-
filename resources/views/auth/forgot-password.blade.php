@extends('layouts.app')

@section('title', 'Quên mật khẩu')

@section('content')
<section class="py-5 profile-section">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-danger">
                                <i class="fas fa-unlock-alt me-2"></i>Quên mật khẩu
                            </h2>
                            <p class="text-muted">Nhập tên đăng nhập và email của bạn để nhận mã OTP.</p>
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

                        <form method="POST" action="{{ route('password.send-otp') }}">
                            @csrf

                            <div class="mb-3">
                                <label for="username" class="form-label fw-semibold">Tên đăng nhập <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg" id="username" name="username" value="{{ old('username') }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control form-control-lg" id="email" name="email" value="{{ old('email') }}" required>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="fas fa-paper-plane me-2"></i>Gửi mã OTP
                            </button>

                            <div class="mt-3 text-center">
                                <a href="{{ route('login') }}">Quay lại đăng nhập</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
