@extends('layouts.admin')

@section('title', 'Chỉnh sửa Giảng viên')
@section('page-title', 'Chỉnh sửa Giảng viên')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Chỉnh sửa thông tin Giảng viên</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.teachers.update', $user) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" 
                               id="username" name="username" value="{{ old('username', $user->username) }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('fullname') is-invalid @enderror" 
                               id="fullname" name="fullname" value="{{ old('fullname', $user->fullname) }}" required>
                        @error('fullname')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Để trống nếu không muốn thay đổi mật khẩu.</div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Xác nhận mật khẩu</label>
                        <input type="password" class="form-control" id="password_confirmation" 
                               name="password_confirmation">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="avatar" class="form-label">Ảnh đại diện</label>
                        <input type="file" class="form-control @error('avatar') is-invalid @enderror" 
                               id="avatar" name="avatar" accept="image/*">
                        @error('avatar')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Chấp nhận: JPEG, PNG, JPG, GIF. Tối đa 2MB.</div>

                        @if($user->avatar)
                            <div class="mt-3">
                                <p class="mb-1"><strong>Ảnh hiện tại:</strong></p>
                                <img src="{{ Storage::url($user->avatar) }}" alt="Avatar" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Cập nhật Giảng viên
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
