@extends('layouts.app')

@section('title', 'Đổi mật khẩu')
@section('page-class', 'page-profile-password')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-7 col-lg-9">
            {{-- Hero --}}
            <div class="card border-0 shadow-sm student-hero-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <div class="text-white-50 small mb-1">Bảo mật tài khoản</div>
                            <h2 class="text-white fw-bold mb-1">Đổi mật khẩu</h2>
                            <p class="text-white-50 mb-0">Đặt mật khẩu mới mạnh để bảo vệ tài khoản của bạn.</p>
                        </div>
                        <a href="{{ route('profile.show') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="alert alert-danger border-0 shadow-sm">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Có lỗi xảy ra:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('profile.change-password') }}" id="change-password-form">
                        @csrf

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold">
                                Mật khẩu hiện tại <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                                <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="Nhập mật khẩu hiện tại">
                                <button class="btn btn-outline-secondary password-toggle" type="button" data-target="current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('current_password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">
                                Mật khẩu mới <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-key text-warning"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required minlength="8" placeholder="Tối thiểu 8 ký tự">
                                <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                            <div class="password-strength mt-2" id="password-strength">
                                <div class="password-strength-bar"><div class="password-strength-fill"></div></div>
                                <small class="text-muted password-strength-label">Nhập mật khẩu để kiểm tra độ mạnh</small>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password_confirmation" class="form-label fw-semibold">
                                Xác nhận mật khẩu mới <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fas fa-check-double text-success"></i></span>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required placeholder="Nhập lại mật khẩu mới">
                                <button class="btn btn-outline-secondary password-toggle" type="button" data-target="password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="small mt-1" id="password-match" style="display:none;"></div>
                        </div>

                        {{-- Tips --}}
                        <div class="alert alert-light border small mb-4">
                            <strong><i class="fas fa-shield-halved text-primary me-2"></i>Mẹo bảo mật:</strong>
                            <ul class="mb-0 mt-1 ps-4">
                                <li>Tối thiểu 8 ký tự, kết hợp chữ hoa, chữ thường, số và ký tự đặc biệt.</li>
                                <li>Không dùng lại mật khẩu của tài khoản khác.</li>
                                <li>Không đặt mật khẩu chứa tên hoặc thông tin cá nhân dễ đoán.</li>
                            </ul>
                        </div>

                        <div class="d-flex flex-wrap gap-2 pt-3 border-top">
                            <button type="submit" class="btn btn-warning">
                                <i class="fas fa-shield-halved me-1"></i>Cập nhật mật khẩu
                            </button>
                            <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary ms-auto">
                                Hủy
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Toggle password visibility on each password input.
    document.querySelectorAll('.password-toggle').forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    // Strength meter for the new password.
    const pw = document.getElementById('password');
    const strengthFill = document.querySelector('.password-strength-fill');
    const strengthLabel = document.querySelector('.password-strength-label');

    function scorePassword(p) {
        if (!p) return 0;
        let score = 0;
        if (p.length >= 8) score++;
        if (p.length >= 12) score++;
        if (/[a-z]/.test(p) && /[A-Z]/.test(p)) score++;
        if (/\d/.test(p)) score++;
        if (/[^A-Za-z0-9]/.test(p)) score++;
        return Math.min(score, 4);
    }

    pw?.addEventListener('input', () => {
        const score = scorePassword(pw.value);
        const pct = [0, 25, 50, 75, 100][score];
        const colors = ['#e2e8f0', '#ef4444', '#f59e0b', '#3b82f6', '#10b981'];
        const labels = [
            'Nhập mật khẩu để kiểm tra độ mạnh',
            'Yếu — nên dài hơn',
            'Trung bình — thêm chữ hoa, số, ký tự đặc biệt',
            'Mạnh',
            'Rất mạnh',
        ];
        strengthFill.style.width = pct + '%';
        strengthFill.style.background = colors[score];
        strengthLabel.textContent = labels[score];
    });

    // Match indicator for confirmation field.
    const confirmField = document.getElementById('password_confirmation');
    const matchEl = document.getElementById('password-match');
    function updateMatch() {
        if (!pw || !confirmField || !matchEl) return;
        if (!confirmField.value) {
            matchEl.style.display = 'none';
            return;
        }
        matchEl.style.display = 'block';
        if (pw.value === confirmField.value) {
            matchEl.innerHTML = '<i class="fas fa-check-circle text-success me-1"></i><span class="text-success">Mật khẩu khớp</span>';
        } else {
            matchEl.innerHTML = '<i class="fas fa-times-circle text-danger me-1"></i><span class="text-danger">Mật khẩu chưa khớp</span>';
        }
    }
    pw?.addEventListener('input', updateMatch);
    confirmField?.addEventListener('input', updateMatch);
</script>
@endsection
