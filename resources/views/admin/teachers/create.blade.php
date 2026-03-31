@extends('layouts.admin')

@section('title', 'Thêm Giảng viên')
@section('page-title', 'Thêm Giảng viên')

@section('content')
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Thông tin Giảng viên Mới</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.teachers.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="username" class="form-label">Tên đăng nhập <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" 
                               id="username" name="username" value="{{ old('username') }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="fullname" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('fullname') is-invalid @enderror" 
                               id="fullname" name="fullname" value="{{ old('fullname') }}" required>
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
                               id="email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password" class="form-label">Mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Xác nhận mật khẩu <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" 
                               name="password_confirmation" required>
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
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.teachers.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Tạo Giảng viên
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
