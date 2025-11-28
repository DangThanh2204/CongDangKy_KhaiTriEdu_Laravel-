@extends('layouts.app')

@section('title', 'Đăng Nhập')

@section('content')
<section class="py-5 auth-section">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary">
                                <i class="fas fa-sign-in-alt me-2"></i>Đăng Nhập
                            </h2>
                            <p class="text-muted">Chào mừng trở lại với Khai Trí Edu</p>
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

                        @if(session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle me-2"></i>
                                {{ session('success') }}
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

                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">Ghi nhớ đăng nhập</label>
                                </div>
                                <a href="#" class="text-primary text-decoration-none fw-semibold">Quên mật khẩu?</a>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-lg py-3 fw-semibold">
                                    <i class="fas fa-sign-in-alt me-2"></i>Đăng Nhập
                                </button>
                            </div>
                        </form>

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
    // Toggle password visibility
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

    // Auto focus on username field
    const usernameField = document.getElementById('username');
    if (usernameField && !usernameField.value) {
        usernameField.focus();
    }
});
</script>
@endsection