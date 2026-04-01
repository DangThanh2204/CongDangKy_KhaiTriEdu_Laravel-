@extends('layouts.app')

@section('title', 'Đăng nhập')

@section('content')
<section class="py-5 auth-section">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            @php
                                $siteLogo = \App\Models\Setting::get('site_logo');
                                $siteName = \App\Models\Setting::get('site_name', 'Khai Tri Edu');
                                $siteTagline = \App\Models\Setting::get('site_tagline', 'Nền tảng học tập trực tuyến');
                            @endphp

                            @if($siteLogo)
                                <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="mb-3" style="max-height: 80px; object-fit: contain;">
                            @endif

                            <h2 class="fw-bold text-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                            </h2>
                            <p class="text-muted">{{ $siteTagline }}</p>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <span class="fw-semibold">Đăng nhập thất bại!</span>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        @if(session('resend_otp'))
                            <div class="alert alert-warning">
                                <i class="fas fa-envelope me-2"></i>
                                Tài khoản chưa được kích hoạt.
                                <a href="{{ route('verify', ['email' => session('resend_otp')]) }}" class="alert-link fw-semibold text-decoration-none">
                                    Nhấn vào đây để xác thực
                                </a>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('login') }}">
                            @csrf

                            <div class="mb-4">
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

                            <div class="mb-4">
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

                            <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
                                <div class="small text-muted">
                                    Phiên đăng nhập sẽ tự kết thúc khi bạn đóng trình duyệt.
                                </div>
                                <a href="{{ route('password.forgot') }}" class="text-primary text-decoration-none fw-semibold">Quên mật khẩu?</a>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg py-3 fw-semibold">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng nhập
                                </button>
                            </div>
                        </form>

                        <div class="d-flex align-items-center my-4">
                            <div class="flex-grow-1 border-top"></div>
                            <span class="px-3 text-muted fw-semibold">Hoặc đăng nhập bằng</span>
                            <div class="flex-grow-1 border-top"></div>
                        </div>

                        <div class="small text-muted text-center mb-3">
                            Nếu Google hoặc Facebook báo chặn quyền truy cập, hãy kiểm tra lại Redirect URI trên trang Developers theo đúng domain hiện tại.
                        </div>

                        <div class="social-links justify-content-center mb-4">
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

                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="mb-0">Chưa có tài khoản?
                                <a href="{{ route('register') }}" class="text-primary fw-bold text-decoration-none">Đăng ký ngay</a>
                            </p>
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
