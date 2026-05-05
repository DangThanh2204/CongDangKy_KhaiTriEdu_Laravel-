@extends('layouts.app')

@section('title', 'Đăng nhập')
@section('page-class', 'page-auth')

@push('styles')
<style>
.auth-section {
    background: linear-gradient(135deg, #eef2f9 0%, #f8fafc 100%);
    min-height: calc(100vh - 80px);
    display: flex;
    align-items: center;
    padding: 2.5rem 0;
}
.auth-card {
    overflow: hidden;
    border-radius: 1rem;
    box-shadow: 0 18px 50px rgba(44, 90, 160, 0.18);
}
.auth-cover {
    background: linear-gradient(140deg, #2c5aa0 0%, #1a3c6e 100%);
    color: #fff;
    position: relative;
    overflow: hidden;
    min-height: 100%;
}
.auth-cover::before {
    content: '';
    position: absolute;
    width: 320px;
    height: 320px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.07);
    top: -120px;
    right: -120px;
}
.auth-cover::after {
    content: '';
    position: absolute;
    width: 220px;
    height: 220px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.05);
    bottom: -80px;
    left: -80px;
}
.auth-cover-inner {
    position: relative;
    z-index: 1;
}
.auth-cover-logo {
    max-height: 56px;
    object-fit: contain;
    filter: brightness(0) invert(1);
}
.auth-form-wrap {
    padding: 2.5rem 2.75rem;
}
.auth-form-wrap .form-label {
    font-size: 0.9rem;
    margin-bottom: 0.35rem;
}
.auth-form-wrap .input-group-text,
.auth-form-wrap .form-control,
.auth-form-wrap .btn {
    padding-top: 0.6rem;
    padding-bottom: 0.6rem;
}
.auth-form-wrap .form-control:focus {
    box-shadow: 0 0 0 0.2rem rgba(44, 90, 160, 0.15);
    border-color: #2c5aa0;
}
.auth-feature-list {
    list-style: none;
    padding: 0;
    margin: 1.5rem 0 0;
}
.auth-feature-list li {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.65rem;
    font-size: 0.9rem;
    opacity: 0.92;
}
.auth-feature-list i {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.15);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
}
.auth-divider {
    display: flex;
    align-items: center;
    margin: 1.25rem 0;
    color: #6c757d;
    font-size: 0.85rem;
}
.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e2e6ee;
}
.auth-divider span {
    padding: 0 0.85rem;
    font-weight: 600;
}
@media (max-width: 991.98px) {
    .auth-form-wrap {
        padding: 2rem 1.75rem;
    }
}
</style>
@endpush

@section('content')
<section class="auth-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-11 col-xl-10">
                <div class="card auth-card border-0">
                    <div class="row g-0">
                        @php
                            $siteLogo = \App\Models\Setting::get('site_logo');
                            $siteName = \App\Models\Setting::get('site_name', 'Khai Tri Edu');
                            $siteTagline = \App\Models\Setting::get('site_tagline', 'Nền tảng học tập trực tuyến');
                        @endphp

                        <div class="col-lg-5 d-none d-lg-block auth-cover">
                            <div class="auth-cover-inner h-100 d-flex flex-column justify-content-between p-5">
                                <div>
                                    <img src="{{ $siteLogo ? asset('storage/' . $siteLogo) : asset('images/logo.png') }}"
                                         alt="{{ $siteName }}"
                                         class="auth-cover-logo mb-4"
                                         onerror="if(this.dataset.fb==='0'){this.dataset.fb='1';this.src='{{ asset('images/logo.png') }}';}else if(this.dataset.fb==='1'){this.dataset.fb='2';this.src='{{ asset('images/logo.svg') }}';}else{this.style.display='none';}"
                                         data-fb="0">
                                    <h3 class="fw-bold mb-2">{{ $siteName }}</h3>
                                    <p class="mb-0 opacity-75">{{ $siteTagline }}</p>
                                </div>
                                <div class="mt-4">
                                    <h4 class="fw-bold mb-2">Chào mừng quay lại!</h4>
                                    <p class="opacity-75 mb-0">Đăng nhập để tiếp tục hành trình học tập của bạn.</p>
                                    <ul class="auth-feature-list">
                                        <li><i class="fas fa-check"></i> Kho khóa học online &amp; offline đa dạng</li>
                                        <li><i class="fas fa-check"></i> Theo dõi tiến độ và nhận chứng chỉ</li>
                                        <li><i class="fas fa-check"></i> Hỗ trợ thanh toán qua ví nội bộ</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-7">
                            <div class="auth-form-wrap">
                                <div class="d-lg-none text-center mb-3">
                                    <img src="{{ $siteLogo ? asset('storage/' . $siteLogo) : asset('images/logo.png') }}"
                                         alt="{{ $siteName }}"
                                         style="max-height:54px;object-fit:contain;"
                                         onerror="if(this.dataset.fb==='0'){this.dataset.fb='1';this.src='{{ asset('images/logo.png') }}';}else{this.style.display='none';}"
                                         data-fb="0">
                                </div>

                                <h2 class="fw-bold text-primary mb-1">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                                </h2>
                                <p class="text-muted mb-4">Nhập tài khoản của bạn để tiếp tục.</p>

                                @if($errors->any())
                                    <div class="alert alert-danger py-2">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <span class="fw-semibold">Đăng nhập thất bại!</span>
                                        <ul class="mb-0 mt-1 small">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                @if(session('resend_otp'))
                                    <div class="alert alert-warning py-2 small">
                                        <i class="fas fa-envelope me-1"></i>
                                        Tài khoản chưa kích hoạt.
                                        <a href="{{ route('verify', ['email' => session('resend_otp')]) }}" class="alert-link fw-semibold text-decoration-none">
                                            Xác thực ngay
                                        </a>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('login') }}">
                                    @csrf

                                    <div class="mb-3">
                                        <label for="username" class="form-label fw-semibold">Tên đăng nhập <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-user text-primary"></i>
                                            </span>
                                            <input type="text" class="form-control @error('username') is-invalid @enderror border-start-0"
                                                   id="username" name="username" value="{{ old('username') }}"
                                                   placeholder="Nhập tên đăng nhập" required>
                                        </div>
                                        @error('username')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label fw-semibold">Mật khẩu <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0">
                                                <i class="fas fa-lock text-primary"></i>
                                            </span>
                                            <input type="password" class="form-control @error('password') is-invalid @enderror border-start-0"
                                                   id="password" name="password" placeholder="Nhập mật khẩu" required>
                                            <button type="button" class="btn btn-outline-secondary border-start-0 toggle-password">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                        @error('password')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="d-flex justify-content-end mb-3">
                                        <a href="{{ route('password.forgot') }}" class="text-primary text-decoration-none fw-semibold small">Quên mật khẩu?</a>
                                    </div>

                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-primary fw-semibold">
                                            <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                                        </button>
                                    </div>
                                </form>

                                <div class="auth-divider"><span>Hoặc đăng nhập bằng</span></div>

                                <div class="social-links justify-content-center mb-3">
                                    <a href="{{ route('auth.google') }}" class="google" title="Đăng nhập bằng Google">
                                        <svg width="20" height="20" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path fill="#4285F4" d="M23.2 12.27c0-.78-.07-1.53-.2-2.25H12v4.26h6.18c-.27 1.44-1.05 2.66-2.25 3.47v2.88h3.64c2.14-1.97 3.38-4.86 3.38-8.36z"/>
                                            <path fill="#34A853" d="M12 24c2.97 0 5.46-1 7.28-2.7l-3.64-2.88c-1.01.68-2.32 1.08-3.64 1.08-2.79 0-5.16-1.88-6-4.42H1.17v2.78C2.95 21.9 7.14 24 12 24z"/>
                                            <path fill="#FBBC05" d="M6 14.08c-.22-.63-.35-1.3-.35-1.98s.13-1.35.35-1.98V7.35H1.17A11.99 11.99 0 0 0 0 12c0 1.92.46 3.74 1.17 5.35L6 14.08z"/>
                                            <path fill="#EA4335" d="M12 4.5c1.62 0 3.08.56 4.23 1.66l3.14-3.14C17.45 1.12 14.96 0 12 0 7.14 0 2.95 2.1 1.17 5.35l4.83 3.35C6.84 6.38 9.21 4.5 12 4.5z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('auth.facebook') }}" class="facebook" title="Đăng nhập bằng Facebook">
                                        <i class="fab fa-facebook-f"></i>
                                    </a>
                                </div>

                                <div class="text-center pt-3 border-top">
                                    <p class="mb-0 small">Chưa có tài khoản?
                                        <a href="{{ route('register') }}" class="text-primary fw-bold text-decoration-none">Đăng ký ngay</a>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.closest('.input-group').querySelector('input');
            const icon = this.querySelector('i');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    const usernameField = document.getElementById('username');
    if (usernameField && !usernameField.value) {
        usernameField.focus();
    }
});
</script>
@endsection
