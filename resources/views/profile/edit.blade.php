@extends('layouts.app')

@section('title', 'Chỉnh sửa thông tin cá nhân')

@section('content')
<section class="py-5 profile-section">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="fw-bold text-primary">
                                <i class="fas fa-edit me-2"></i>Chỉnh sửa thông tin cá nhân
                            </h2>
                            <p class="text-muted">Cập nhật thông tin tài khoản của bạn</p>
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

                        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-4 text-center mb-4">
                                    <div class="avatar-container">
                                        @if($user->avatar)
                                            <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->fullname }}" class="rounded-circle avatar-preview mb-3" style="width: 120px; height: 120px; object-fit: cover; border: 4px solid #e9ecef;">
                                        @else
                                            <div class="avatar-placeholder rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 120px; height: 120px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 3rem;">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <label for="avatar" class="form-label fw-semibold">Ảnh đại diện</label>
                                            <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                            <div class="form-text">Chọn ảnh JPG, PNG hoặc GIF, tối đa 2MB</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="mb-4">
                                        <label for="fullname" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-lg" id="fullname" name="fullname"
                                               value="{{ old('fullname', $user->fullname) }}" required>
                                        @error('fullname')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label for="username" class="form-label fw-semibold">Tên đăng nhập</label>
                                        <input type="text" class="form-control form-control-lg" id="username"
                                               value="{{ $user->username }}" readonly disabled>
                                        <div class="form-text">Tên đăng nhập không thể thay đổi</div>
                                    </div>

                                    <div class="mb-4">
                                        <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control form-control-lg" id="email" name="email"
                                               value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Trạng thái tài khoản</label>
                                        <div>
                                            <span class="badge bg-{{ $user->is_verified ? 'success' : 'warning' }} fs-6">
                                                <i class="fas fa-{{ $user->is_verified ? 'check-circle' : 'clock' }} me-1"></i>
                                                {{ $user->is_verified ? 'Đã xác thực' : 'Chưa xác thực' }}
                                            </span>
                                        </div>
                                        <div class="form-text">
                                            @if(!$user->is_verified)
                                                Tài khoản của bạn chưa được xác thực. Vui lòng kiểm tra email để xác thực.
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-4">
                                <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                                </a>

                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save me-2"></i>Lưu thay đổi
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Preview avatar khi chọn file
document.getElementById('avatar').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.querySelector('.avatar-preview');
            if (preview) {
                preview.src = e.target.result;
            } else {
                // Nếu chưa có avatar, tạo mới
                const container = document.querySelector('.avatar-container');
                const img = document.createElement('img');
                img.src = e.target.result;
                img.className = 'rounded-circle avatar-preview mb-3';
                img.style.width = '120px';
                img.style.height = '120px';
                img.style.objectFit = 'cover';
                img.style.border = '4px solid #e9ecef';

                const placeholder = container.querySelector('.avatar-placeholder');
                if (placeholder) {
                    container.replaceChild(img, placeholder);
                }
            }
        };
        reader.readAsDataURL(file);
    }
});
    // Preview avatar khi chọn file
    document.getElementById('avatar').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.querySelector('.avatar-preview');
                if (preview) {
                    preview.src = e.target.result;
                } else {
                    // Nếu chưa có avatar, tạo mới
                    const container = document.querySelector('.avatar-container');
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = 'rounded-circle avatar-preview mb-3';
                    img.style.width = '120px';
                    img.style.height = '120px';
                    img.style.objectFit = 'cover';
                    img.style.border = '4px solid #e9ecef';

                    const placeholder = container.querySelector('.avatar-placeholder');
                    if (placeholder) {
                        container.replaceChild(img, placeholder);
                    }
                }
            };
            reader.readAsDataURL(file);
        }
    });

    // Automatically expand change password card if navigated here or validation errors exist
    document.addEventListener('DOMContentLoaded', function() {
        var shouldOpen = false;
        if (window.location.hash === '#change-password-card') {
            shouldOpen = true;
        }
        @if ($errors->has('current_password') || $errors->has('password'))
            shouldOpen = true;
        @endif
        if (shouldOpen) {
            var collapseEl = document.getElementById('passwordCollapse');
            if (collapseEl) {
                var bsCollapse = new bootstrap.Collapse(collapseEl, {toggle: true});
            }
        }
    });
</script>
@endsection