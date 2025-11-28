@extends('layouts.admin')

@section('title', 'Chỉnh sửa Khóa học')
@section('page-title', 'Chỉnh sửa Khóa học')

@section('content')
<div class="admin-content">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-edit me-2"></i>Chỉnh sửa khóa học: <span class="text-primary">{{ $course->title }}</span>
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.courses.update', $course) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <div class="col-md-8">
                        <!-- Basic Information Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-info-circle me-2"></i>Thông tin cơ bản
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="title" class="form-label">
                                        <strong>Tiêu đề khóa học</strong> <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                           id="title" name="title" value="{{ old('title', $course->title) }}" 
                                           placeholder="Nhập tiêu đề khóa học" required>
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="short_description" class="form-label">
                                        <strong>Mô tả ngắn</strong> <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('short_description') is-invalid @enderror" 
                                              id="short_description" name="short_description" rows="2" 
                                              placeholder="Mô tả ngắn về khóa học (tối đa 500 ký tự)" 
                                              maxlength="500" required>{{ old('short_description', $course->short_description) }}</textarea>
                                    @error('short_description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text"><span id="short_desc_counter">0</span>/500 ký tự</div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        <strong>Mô tả chi tiết</strong> <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="5" 
                                              placeholder="Mô tả chi tiết về khóa học" required>{{ old('description', $course->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Media Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-images me-2"></i>Hình ảnh
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="thumbnail" class="form-label">
                                                <strong>Ảnh thumbnail</strong>
                                            </label>
                                            @if($course->thumbnail)
                                            <div class="mb-2">
                                                <img src="{{ $course->thumbnail_url }}" alt="Thumbnail" 
                                                     class="rounded" style="max-width: 200px; max-height: 150px;">
                                            </div>
                                            @endif
                                            <input type="file" class="form-control @error('thumbnail') is-invalid @enderror" 
                                                   id="thumbnail" name="thumbnail" accept="image/*">
                                            @error('thumbnail')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Kích thước đề xuất: 400x300px</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="banner_image" class="form-label">
                                                <strong>Ảnh banner</strong>
                                            </label>
                                            @if($course->banner_image)
                                            <div class="mb-2">
                                                <img src="{{ $course->banner_image_url }}" alt="Banner" 
                                                     class="rounded" style="max-width: 200px; max-height: 150px;">
                                            </div>
                                            @endif
                                            <input type="file" class="form-control @error('banner_image') is-invalid @enderror" 
                                                   id="banner_image" name="banner_image" accept="image/*">
                                            @error('banner_image')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Kích thước đề xuất: 1200x400px</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Settings Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-cog me-2"></i>Cài đặt
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="category_id" class="form-label">
                                        <strong>Danh mục</strong> <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('category_id') is-invalid @enderror" 
                                            id="category_id" name="category_id" required>
                                        <option value="">-- Chọn danh mục --</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('category_id', $course->category_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="instructor_id" class="form-label">
                                        <strong>Giảng viên</strong> <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('instructor_id') is-invalid @enderror" 
                                            id="instructor_id" name="instructor_id" required>
                                        <option value="">-- Chọn giảng viên --</option>
                                        @foreach($instructors as $instructor)
                                        <option value="{{ $instructor->id }}" {{ old('instructor_id', $course->instructor_id) == $instructor->id ? 'selected' : '' }}>
                                            {{ $instructor->fullname }} ({{ $instructor->email }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('instructor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="level" class="form-label">
                                        <strong>Cấp độ</strong> <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('level') is-invalid @enderror" 
                                            id="level" name="level" required>
                                        <option value="">-- Chọn cấp độ --</option>
                                        <option value="beginner" {{ old('level', $course->level) == 'beginner' ? 'selected' : '' }}>Người mới bắt đầu</option>
                                        <option value="intermediate" {{ old('level', $course->level) == 'intermediate' ? 'selected' : '' }}>Trung cấp</option>
                                        <option value="advanced" {{ old('level', $course->level) == 'advanced' ? 'selected' : '' }}>Nâng cao</option>
                                        <option value="all" {{ old('level', $course->level) == 'all' ? 'selected' : '' }}>Tất cả cấp độ</option>
                                    </select>
                                    @error('level')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="duration" class="form-label">
                                        <strong>Thời lượng (giờ)</strong> <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control @error('duration') is-invalid @enderror" 
                                           id="duration" name="duration" value="{{ old('duration', $course->duration) }}" 
                                           min="1" max="1000" step="1" placeholder="Ví dụ: 10" required>
                                    @error('duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Pricing Card -->
                        <div class="card mb-4">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-tag me-2"></i>Giá cả
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="price" class="form-label">
                                        <strong>Giá gốc (VNĐ)</strong> <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror" 
                                           id="price" name="price" value="{{ old('price', $course->price) }}" 
                                           min="0" step="1000" placeholder="0" required>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="sale_price" class="form-label">
                                        <strong>Giá khuyến mãi (VNĐ)</strong>
                                    </label>
                                    <input type="number" class="form-control @error('sale_price') is-invalid @enderror" 
                                           id="sale_price" name="sale_price" value="{{ old('sale_price', $course->sale_price) }}" 
                                           min="0" step="1000" placeholder="0">
                                    @error('sale_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Để trống nếu không có khuyến mãi</div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Card -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-eye me-2"></i>Trạng thái & Tính năng
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="status" class="form-label">
                                        <strong>Trạng thái</strong> <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('status') is-invalid @enderror" 
                                            id="status" name="status" required>
                                        <option value="draft" {{ old('status', $course->status) == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                                        <option value="published" {{ old('status', $course->status) == 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_featured" name="is_featured" value="1" 
                                               {{ old('is_featured', $course->is_featured) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium" for="is_featured">
                                            Đánh dấu nổi bật
                                        </label>
                                    </div>
                                    <div class="form-text">Khóa học nổi bật sẽ hiển thị ở trang chủ</div>
                                </div>

                                <div class="mb-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_popular" name="is_popular" value="1" 
                                               {{ old('is_popular', $course->is_popular) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium" for="is_popular">
                                            Đánh dấu phổ biến
                                        </label>
                                    </div>
                                    <div class="form-text">Khóa học phổ biến sẽ được ưu tiên hiển thị</div>
                                </div>

                                <!-- Course Information -->
                                <div class="mt-4 p-3 bg-light rounded">
                                    <h6 class="mb-2">
                                        <i class="fas fa-chart-bar me-2"></i>Thống kê
                                    </h6>
                                    <div class="small">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Học viên:</span>
                                            <strong>{{ $course->students_count }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span>Đánh giá:</span>
                                            <strong>{{ $course->rating }} ({{ $course->total_rating }})</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Ngày tạo:</span>
                                            <strong>{{ $course->created_at->format('d/m/Y') }}</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                            </a>
                            <div>
                                <a href="{{ route('admin.courses.create') }}" class="btn btn-outline-success me-2">
                                    <i class="fas fa-plus me-2"></i>Thêm mới
                                </a>
                                <button type="reset" class="btn btn-outline-danger me-2">
                                    <i class="fas fa-undo me-2"></i>Hoàn tác
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Cập nhật khóa học
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Character counter for short description
    const shortDescInput = document.getElementById('short_description');
    const shortDescCounter = document.getElementById('short_desc_counter');
    
    shortDescInput.addEventListener('input', function() {
        shortDescCounter.textContent = this.value.length;
    });

    // Initialize counter
    shortDescCounter.textContent = shortDescInput.value.length;

    // Price validation
    const priceInput = document.getElementById('price');
    const salePriceInput = document.getElementById('sale_price');
    
    salePriceInput.addEventListener('input', function() {
        if (this.value && parseFloat(this.value) >= parseFloat(priceInput.value)) {
            this.setCustomValidity('Giá khuyến mãi phải nhỏ hơn giá gốc');
        } else {
            this.setCustomValidity('');
        }
    });
});
</script>
@endsection