@extends('layouts.app')

@section('title', 'Đăng Ký')

@section('content')
<section class="py-5 auth-section">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary">
                                <i class="fas fa-user-plus me-2"></i>Đăng Ký Tài Khoản
                            </h2>
                            <p class="text-muted">Tham gia hệ thống giáo dục Khai Trí</p>
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

                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('register') }}">
                            @csrf

                            <div class="row g-3">
                                <!-- Username -->
                                <div class="col-md-6">
                                    <label for="username" class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
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

                                <!-- Full Name -->
                                <div class="col-md-6">
                                    <label for="fullname" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-id-card text-primary"></i>
                                        </span>
                                        <input type="text" class="form-control @error('fullname') is-invalid @enderror border-start-0" 
                                               id="fullname" name="fullname" value="{{ old('fullname') }}" 
                                               placeholder="Nhập họ và tên" required>
                                    </div>
                                    @error('fullname')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div class="col-12">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-envelope text-primary"></i>
                                        </span>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror border-start-0" 
                                               id="email" name="email" value="{{ old('email') }}" 
                                               placeholder="Nhập email" required>
                                    </div>
                                    @error('email')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Password -->
                                <div class="col-md-6">
                                    <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
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
                                    <small class="form-text text-muted">Mật khẩu tối thiểu 8 ký tự</small>
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0">
                                            <i class="fas fa-lock text-primary"></i>
                                        </span>
                                        <input type="password" class="form-control border-start-0" 
                                               id="password_confirmation" name="password_confirmation" 
                                               placeholder="Xác nhận mật khẩu" required>
                                        <button type="button" class="btn btn-outline-secondary border-start-0 toggle-password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Terms Agreement -->
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="agreeTerms" name="agreeTerms" required>
                                <label class="form-check-label" for="agreeTerms">
                                    Tôi đồng ý với <a href="#" class="text-primary text-decoration-none">Điều khoản dịch vụ</a> và <a href="#" class="text-primary text-decoration-none">Chính sách bảo mật</a>
                                </label>
                            </div>

                            <!-- Submit Button -->
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg py-3 fw-semibold">
                                    <i class="fas fa-user-plus me-2"></i>Đăng Ký
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="mb-0">Đã có tài khoản? 
                                <a href="{{ route('login') }}" class="text-primary fw-bold text-decoration-none">Đăng nhập ngay</a>
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

    // Real-time password strength check
    const passwordInput = document.getElementById('password');
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthText = this.parentElement.nextElementSibling;
            
            if (password.length === 0) {
                strengthText.className = 'form-text text-muted';
                strengthText.textContent = 'Mật khẩu tối thiểu 8 ký tự';
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