@extends('layouts.app')

@section('title', 'Chỉnh sửa thông tin cá nhân')
@section('page-class', 'page-profile-edit')

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-xl-9 col-lg-10">
            {{-- Hero --}}
            <div class="card border-0 shadow-sm student-hero-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <div class="text-white-50 small mb-1">Thông tin cá nhân</div>
                            <h2 class="text-white fw-bold mb-1">Chỉnh sửa hồ sơ</h2>
                            <p class="text-white-50 mb-0">Cập nhật ảnh đại diện, họ tên và email liên hệ.</p>
                        </div>
                        <a href="{{ route('profile.show') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>

            {{-- Errors --}}
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

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    {{-- Avatar card --}}
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4 text-center">
                                <h6 class="fw-bold mb-3"><i class="fas fa-camera text-primary me-2"></i>Ảnh đại diện</h6>
                                <div class="profile-edit-avatar mx-auto mb-3">
                                    @if($user->avatar)
                                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->fullname }}" class="profile-edit-avatar-image avatar-preview">
                                    @else
                                        <div class="profile-edit-avatar-placeholder">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                </div>
                                <label for="avatar" class="btn btn-outline-primary btn-sm w-100">
                                    <i class="fas fa-upload me-1"></i>Chọn ảnh mới
                                </label>
                                <input type="file" class="d-none" id="avatar" name="avatar" accept="image/*">
                                <div class="form-text mt-2">JPG, PNG hoặc GIF · tối đa 2MB</div>
                                @error('avatar')
                                    <div class="text-danger small mt-2">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Form fields --}}
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-body p-4">
                                <h6 class="fw-bold mb-3"><i class="fas fa-user-edit text-primary me-2"></i>Thông tin cơ bản</h6>

                                <div class="mb-3">
                                    <label for="fullname" class="form-label fw-semibold">Họ và tên <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                        <input type="text" class="form-control" id="fullname" name="fullname"
                                               value="{{ old('fullname', $user->fullname) }}" required placeholder="Nhập họ và tên đầy đủ">
                                    </div>
                                    @error('fullname')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="username" class="form-label fw-semibold">Tên đăng nhập</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-at text-muted"></i></span>
                                        <input type="text" class="form-control" id="username"
                                               value="{{ $user->username }}" readonly disabled>
                                    </div>
                                    <div class="form-text">Tên đăng nhập không thể thay đổi sau khi đăng ký.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                        <input type="email" class="form-control" id="email" name="email"
                                               value="{{ old('email', $user->email) }}" required placeholder="email@example.com">
                                    </div>
                                    @error('email')
                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-2">
                                    <label class="form-label fw-semibold">Trạng thái xác thực</label>
                                    <div>
                                        @if($user->is_verified)
                                            <span class="badge bg-success-subtle text-success-emphasis px-3 py-2">
                                                <i class="fas fa-check-circle me-1"></i>Đã xác thực
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis px-3 py-2">
                                                <i class="fas fa-clock me-1"></i>Chưa xác thực — Vui lòng kiểm tra email
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2 mt-4 pt-3 border-top">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>Lưu thay đổi
                                    </button>
                                    <a href="{{ route('profile.change-password.form') }}" class="btn btn-outline-warning">
                                        <i class="fas fa-key me-1"></i>Đổi mật khẩu
                                    </a>
                                    <a href="{{ route('profile.show') }}" class="btn btn-outline-secondary ms-auto">
                                        Hủy
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Live preview avatar khi chọn file mới.
    document.getElementById('avatar').addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (ev) {
            const wrap = document.querySelector('.profile-edit-avatar');
            if (!wrap) return;

            wrap.innerHTML = '';
            const img = document.createElement('img');
            img.src = ev.target.result;
            img.alt = 'Ảnh đại diện mới';
            img.className = 'profile-edit-avatar-image avatar-preview';
            wrap.appendChild(img);
        };
        reader.readAsDataURL(file);
    });
</script>
@endsection
