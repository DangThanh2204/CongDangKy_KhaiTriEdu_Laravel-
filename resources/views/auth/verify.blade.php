@extends('layouts.app')

@section('title', 'Xác thực OTP')

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
                                $siteName = \App\Models\Setting::get('site_name', 'Khai Trí Education');
                            @endphp

                            @if($siteLogo)
                                <img src="{{ asset('storage/' . $siteLogo) }}" alt="{{ $siteName }}" class="mb-3" style="max-height: 80px; object-fit: contain;">
                            @else
                                <div class="verify-icon mb-3">
                                    <i class="fas fa-shield-alt fa-3x text-primary"></i>
                                </div>
                            @endif

                            <h2 class="fw-bold text-primary">Xác thực OTP</h2>
                            <p class="text-muted mb-1">Vui lòng nhập mã OTP đã được gửi đến email của bạn</p>
                            <p class="text-info small mb-0">
                                <i class="fas fa-envelope me-1"></i>{{ $email }}
                            </p>
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
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        @if(session('error'))
                            <div class="alert alert-danger">{{ session('error') }}</div>
                        @endif

                        <form method="POST" action="{{ route('verify') }}">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">

                            <div class="mb-4">
                                <label for="otp" class="form-label">Mã OTP <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                    <input type="text" class="form-control @error('otp') is-invalid @enderror"
                                           id="otp" name="otp" placeholder="Nhập 6 số OTP"
                                           maxlength="6" pattern="[0-9]{6}" required
                                           value="{{ old('otp') }}">
                                </div>
                                @error('otp')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Mã OTP có hiệu lực trong 10 phút. Nếu quá hạn, tài khoản chưa xác thực sẽ được dọn khỏi hệ thống.</small>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-circle me-2"></i>Xác thực
                                </button>

                                <button type="button" class="btn btn-outline-secondary" id="resendOtp">
                                    <i class="fas fa-redo me-2"></i>Gửi lại OTP
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <p class="mb-0">
                                <a href="{{ route('register') }}" class="text-primary">
                                    <i class="fas fa-arrow-left me-1"></i>Quay lại đăng ký
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<style>
.verify-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: rgba(44, 90, 160, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const otpInput = document.getElementById('otp');
    const resendBtn = document.getElementById('resendOtp');
    let countdown = 60;
    let timer;

    if (otpInput) {
        otpInput.focus();

        otpInput.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        otpInput.addEventListener('input', function() {
            if (this.value.length === 6) {
                this.form.submit();
            }
        });
    }

    function startCountdown() {
        resendBtn.disabled = true;
        resendBtn.innerHTML = `<i class="fas fa-clock me-2"></i>Gửi lại sau ${countdown}s`;

        timer = setInterval(() => {
            countdown--;
            resendBtn.innerHTML = `<i class="fas fa-clock me-2"></i>Gửi lại sau ${countdown}s`;

            if (countdown <= 0) {
                clearInterval(timer);
                resendBtn.disabled = false;
                resendBtn.innerHTML = `<i class="fas fa-redo me-2"></i>Gửi lại OTP`;
                countdown = 60;
            }
        }, 1000);
    }

    resendBtn.addEventListener('click', function() {
        fetch('{{ route('resend.otp') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ email: '{{ $email }}' })
        })
        .then(async response => {
            const data = await response.json().catch(() => ({
                success: false,
                message: 'Không thể gửi lại OTP. Vui lòng thử lại.'
            }));

            if (!response.ok || !data.success) {
                throw new Error(data.message || 'Không thể gửi lại OTP.');
            }

            return data;
        })
        .then(data => {
            alert(data.message);
            startCountdown();
        })
        .catch(error => {
            alert(error.message || 'Có lỗi xảy ra, vui lòng thử lại!');
        });
    });

    startCountdown();
});
</script>
@endsection