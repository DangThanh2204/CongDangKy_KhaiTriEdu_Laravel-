@extends('layouts.app')

@section('title', 'Đăng ký')
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
    padding: 2.25rem 2.5rem;
}
.auth-form-wrap .form-label {
    font-size: 0.88rem;
    margin-bottom: 0.3rem;
}
.auth-form-wrap .input-group-text,
.auth-form-wrap .form-control,
.auth-form-wrap .btn {
    padding-top: 0.55rem;
    padding-bottom: 0.55rem;
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
@media (max-width: 991.98px) {
    .auth-form-wrap {
        padding: 1.75rem 1.5rem;
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
                                    <h4 class="fw-bold mb-2">Bắt đầu hành trình của bạn</h4>
                                    <p class="opacity-75 mb-0">Tạo tài khoản miễn phí để truy cập kho khóa học.</p>
                                    <ul class="auth-feature-list">
                                        <li><i class="fas fa-check"></i> Đăng ký khóa học chỉ trong vài phút</li>
                                        <li><i class="fas fa-check"></i> Học mọi lúc, mọi nơi trên đa thiết bị</li>
                                        <li><i class="fas fa-check"></i> Nhận chứng chỉ sau khi hoàn thành</li>
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
                                    <i class="fas fa-user-plus me-2"></i>Đăng ký tài khoản
                                </h2>
                                <p class="text-muted mb-3">Tham gia {{ $siteName }} để bắt đầu học tập.</p>

                                @if($errors->any())
                                    <div class="alert alert-danger py-2">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        <span class="fw-semibold">Đăng ký chưa thành công.</span>
                                        <ul class="mb-0 mt-1 small">
                                            @foreach($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif

                                <form method="POST" action="{{ route('register') }}">
                                    @csrf

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label for="username" class="form-label fw-semibold">Tên đăng nhập <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-user text-primary"></i>
                                                </span>
                                                <input type="text" class="form-control @error('username') is-invalid @enderror border-start-0"
                                                       id="username" name="username" value="{{ old('username') }}"
                                                       placeholder="Tên đăng nhập" required>
                                            </div>
                                            @error('username')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="fullname" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-id-card text-primary"></i>
                                                </span>
                                                <input type="text" class="form-control @error('fullname') is-invalid @enderror border-start-0"
                                                       id="fullname" name="fullname" value="{{ old('fullname') }}"
                                                       placeholder="Họ và tên" required>
                                            </div>
                                            @error('fullname')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-12">
                                            <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-envelope text-primary"></i>
                                                </span>
                                                <input type="email" class="form-control @error('email') is-invalid @enderror border-start-0"
                                                       id="email" name="email" value="{{ old('email') }}"
                                                       placeholder="email@example.com" required>
                                            </div>
                                            @error('email')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-6">
                                            <label for="password" class="form-label fw-semibold">Mật khẩu <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-lock text-primary"></i>
                                                </span>
                                                <input type="password" class="form-control @error('password') is-invalid @enderror border-start-0"
                                                       id="password" name="password" placeholder="Tối thiểu 8 ký tự" required>
                                                <button type="button" class="btn btn-outline-secondary border-start-0 toggle-password">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            @error('password')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">Mật khẩu tối thiểu 8 ký tự.</small>
                                        </div>

                                        <div class="col-md-6">
                                            <label for="password_confirmation" class="form-label fw-semibold">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0">
                                                    <i class="fas fa-lock text-primary"></i>
                                                </span>
                                                <input type="password" class="form-control border-start-0"
                                                       id="password_confirmation" name="password_confirmation"
                                                       placeholder="Nhập lại mật khẩu" required>
                                                <button type="button" class="btn btn-outline-secondary border-start-0 toggle-password">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-check mt-3">
                                        <input class="form-check-input" type="checkbox" id="agreeTerms" name="agreeTerms" required>
                                        <label class="form-check-label small" for="agreeTerms">
                                            Tôi đồng ý với <a href="#" class="text-primary text-decoration-none">Điều khoản dịch vụ</a> và <a href="#" class="text-primary text-decoration-none">Chính sách bảo mật</a>
                                        </label>
                                    </div>

                                    <div class="d-grid mt-3">
                                        <button type="submit" class="btn btn-primary fw-semibold">
                                            <i class="fas fa-user-plus me-2"></i>Đăng ký
                                        </button>
                                    </div>
                                </form>

                                <div class="text-center mt-3 pt-3 border-top">
                                    <p class="mb-0 small">Đã có tài khoản?
                                        <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">Đăng nhập ngay</a>
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

    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthText = this.parentElement.nextElementSibling;
            if (!strengthText) return;

            if (password.length === 0) {
                strengthText.className = 'form-text text-muted';
                strengthText.textContent = 'Mật khẩu tối thiểu 8 ký tự.';
            } else if (password.length < 8) {
                strengthText.className = 'form-text text-warning';
                strengthText.textContent = 'Mật khẩu yếu';
            } else if (password.length < 12) {
                strengthText.className = 'form-text text-info';
                strengthText.textContent = 'Mật khẩu trung bình';
            } else {
                strengthText.className = 'form-text text-success';
                strengthText.textContent = 'Mật khẩu mạnh';
            }
        });
    }
});
</script>
@endsection
