@extends('layouts.admin')

@section('title', 'Thêm học viên vào khóa học')
@section('page-title', 'Thêm học viên vào khóa học')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-user-plus me-2"></i>Thêm học viên vào khóa học
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.enrollments.manual-enroll') }}">
                    @csrf

                    <!-- Khóa học -->
                    <div class="mb-3">
                        <label for="course_id" class="form-label">Chọn khóa học <span class="text-danger">*</span></label>
                        <select name="course_id" id="course_id" class="form-select" required>
                            <option value="">-- Chọn khóa học --</option>
                            @foreach($courses as $course)
                            <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                                {{ $course->title }} ({{ $course->category->name ?? 'N/A' }})
                            </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Thông tin học viên -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fullname" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="fullname" name="fullname" 
                                       value="{{ old('fullname') }}" required placeholder="Nhập họ và tên đầy đủ">
                                @error('fullname')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="{{ old('email') }}" required placeholder="Nhập email">
                                @error('email')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-3">
                        <label for="notes" class="form-label">Ghi chú</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2" 
                                  placeholder="Ghi chú về học viên...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Auto approve -->
                    <div class="mb-4">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" id="auto_approve" name="auto_approve" value="1" checked>
                            <label class="form-check-label" for="auto_approve">
                                <strong>Tự động duyệt và cho học ngay</strong>
                                <small class="d-block text-muted">Nếu bật, học viên sẽ được thêm vào khóa học ngay lập tức</small>
                            </label>
                        </div>
                    </div>

                    <!-- Buttons -->
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.enrollments.pending') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Thêm học viên
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Thông tin hướng dẫn -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-info-circle me-2 text-primary"></i>Hướng dẫn sử dụng</h6>
                <ul class="small text-muted mb-0">
                    <li><strong>Hệ thống sẽ tự động tạo username từ tên học viên</strong> (ví dụ: "Nguyễn Văn A" → "nguyena")</li>
                    <li>Nếu username đã tồn tại, hệ thống sẽ tự động thêm số (ví dụ: "nguyena1", "nguyena2")</li>
                    <li>Mật khẩu sẽ được tạo ngẫu nhiên, học viên có thể dùng tính năng "Quên mật khẩu" để đặt lại</li>
                    <li>Nếu bật "Tự động duyệt", học viên sẽ được thêm vào khóa học ngay lập tức</li>
                    <li>Nếu tắt "Tự động duyệt", học viên sẽ ở trạng thái chờ duyệt</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto fill username from email
    const emailInput = document.getElementById('email');
    const fullnameInput = document.getElementById('fullname');
    
    emailInput.addEventListener('blur', function() {
        if (emailInput.value && !fullnameInput.value) {
            // Gợi ý tên từ email (phần trước @)
            const suggestedName = emailInput.value.split('@')[0];
            const formattedName = suggestedName
                .replace(/[._-]/g, ' ')
                .replace(/\b\w/g, l => l.toUpperCase());
            
            fullnameInput.value = formattedName;
            fullnameInput.focus();
        }
    });
});
</script>
@endsection