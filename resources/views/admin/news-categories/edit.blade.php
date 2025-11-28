@extends('layouts.admin')

@section('title', 'Chỉnh sửa Danh mục Tin tức')
@section('page-title', 'Chỉnh sửa Danh mục Tin tức')

@section('content')
<div class="admin-content">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-edit me-2"></i>Chỉnh sửa danh mục: <span class="text-primary">{{ $category->name }}</span>
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.news-categories.update', $category) }}" method="POST">
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
                                    <label for="name" class="form-label">
                                        <strong>Tên danh mục</strong> <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name', $category->name) }}" 
                                           placeholder="Nhập tên danh mục" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Thay đổi tên danh mục sẽ cập nhật tự động slug URL.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        <strong>Mô tả</strong>
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" 
                                              placeholder="Nhập mô tả ngắn về danh mục">{{ old('description', $category->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Mô tả giúp cải thiện SEO và trải nghiệm người dùng.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Settings Card -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-cog me-2"></i>Cài đặt hiển thị
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="color" class="form-label">
                                                <strong>Màu sắc</strong> <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group color-picker-group">
                                                <input type="color" class="form-control form-control-color @error('color') is-invalid @enderror" 
                                                       id="color" name="color" value="{{ old('color', $category->color) }}" 
                                                       title="Chọn màu sắc cho danh mục">
                                                <input type="text" class="form-control color-hex-input @error('color') is-invalid @enderror" 
                                                       id="color_hex" name="color_hex" value="{{ old('color', $category->color) }}" 
                                                       pattern="^#[0-9A-Fa-f]{6}$" 
                                                       placeholder="#2c5aa0" maxlength="7">
                                                <span class="input-group-text">
                                                    <i class="fas fa-palette"></i>
                                                </span>
                                            </div>
                                            @error('color')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Màu sắc đại diện cho danh mục.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="order" class="form-label">
                                                <strong>Thứ tự hiển thị</strong>
                                            </label>
                                            <input type="number" class="form-control @error('order') is-invalid @enderror" 
                                                   id="order" name="order" value="{{ old('order', $category->order) }}" 
                                                   min="0" max="999" step="1">
                                            @error('order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Số nhỏ hơn sẽ hiển thị trước.</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Status & Information Card -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-chart-bar me-2"></i>Thông tin & Trạng thái
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Category Information -->
                                <div class="mb-4">
                                    <label class="form-label"><strong>Thông tin danh mục</strong></label>
                                    <div class="info-item mb-2">
                                        <small class="text-muted">Slug:</small>
                                        <div>
                                            <code class="text-primary">{{ $category->slug }}</code>
                                        </div>
                                    </div>
                                    <div class="info-item mb-2">
                                        <small class="text-muted">Tổng bài viết:</small>
                                        <div>
                                            <span class="badge bg-info">{{ $category->posts_count }} bài viết</span>
                                        </div>
                                    </div>
                                    <div class="info-item mb-3">
                                        <small class="text-muted">Ngày tạo:</small>
                                        <div>
                                            <small class="text-muted">{{ $category->created_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <small class="text-muted">Cập nhật cuối:</small>
                                        <div>
                                            <small class="text-muted">{{ $category->updated_at->format('d/m/Y H:i') }}</small>
                                        </div>
                                    </div>
                                </div>

                                <!-- Status Settings -->
                                <div class="mb-4">
                                    <label class="form-label"><strong>Trạng thái</strong></label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', $category->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium" for="is_active">
                                            Kích hoạt danh mục
                                        </label>
                                    </div>
                                    <div class="form-text">
                                        @if($category->is_active)
                                            <i class="fas fa-check-circle text-success me-1"></i>
                                            Danh mục đang hiển thị trên website
                                        @else
                                            <i class="fas fa-pause-circle text-warning me-1"></i>
                                            Danh mục đang bị ẩn
                                        @endif
                                    </div>
                                </div>

                                <!-- Preview Section -->
                                <div class="preview-section">
                                    <label class="form-label"><strong>Xem trước</strong></label>
                                    <div class="preview-category p-3 rounded border">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge me-2 preview-badge" 
                                                  style="background: {{ old('color', $category->color) }}; color: white;">
                                                <span class="preview-name">{{ old('name', $category->name) ?: $category->name }}</span>
                                            </span>
                                            <small class="text-muted preview-count">{{ $category->posts_count }} bài viết</small>
                                        </div>
                                        <p class="preview-description mb-0 small text-muted">
                                            {{ old('description', $category->description) ?: ($category->description ?: 'Mô tả danh mục sẽ hiển thị ở đây...') }}
                                        </p>
                                    </div>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Đây là cách danh mục sẽ hiển thị sau khi cập nhật.
                                    </div>
                                </div>

                                <!-- Danger Zone -->
                                @if($category->posts_count == 0)
                                <div class="mt-4 p-3 bg-light rounded border">
                                    <h6 class="mb-2 text-danger">
                                        <i class="fas fa-exclamation-triangle me-2"></i>Vùng nguy hiểm
                                    </h6>
                                    <p class="small mb-2">Bạn có thể xóa danh mục này vì nó không chứa bài viết nào.</p>
                                    <form action="{{ route('admin.news-categories.destroy', $category) }}" 
                                          method="POST" 
                                          onsubmit="return confirm('Bạn có chắc chắn muốn xóa danh mục {{ $category->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                            <i class="fas fa-trash me-1"></i>Xóa danh mục
                                        </button>
                                    </form>
                                </div>
                                @else
                                <div class="mt-4 p-3 bg-warning bg-opacity-10 rounded border border-warning">
                                    <h6 class="mb-2 text-warning">
                                        <i class="fas fa-info-circle me-2"></i>Lưu ý
                                    </h6>
                                    <p class="small mb-0">Không thể xóa danh mục này vì đang chứa {{ $category->posts_count }} bài viết.</p>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('admin.news-categories.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                            </a>
                            <div>
                                <a href="{{ route('admin.news-categories.create') }}" class="btn btn-outline-success me-2">
                                    <i class="fas fa-plus me-2"></i>Thêm mới
                                </a>
                                <button type="reset" class="btn btn-outline-danger me-2">
                                    <i class="fas fa-undo me-2"></i>Hoàn tác
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Cập nhật danh mục
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
    const colorInput = document.getElementById('color');
    const colorHexInput = document.getElementById('color_hex');
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const previewBadge = document.querySelector('.preview-badge');
    const previewName = document.querySelector('.preview-name');
    const previewDescription = document.querySelector('.preview-description');
    const previewCategory = document.querySelector('.preview-category');

    // Update preview function
    function updatePreview() {
        const color = colorInput.value;
        const name = nameInput.value || '{{ $category->name }}';
        const description = descriptionInput.value || '{{ $category->description }}' || 'Mô tả danh mục sẽ hiển thị ở đây...';

        // Update color
        previewBadge.style.backgroundColor = color;
        previewCategory.style.borderColor = color + '40';
        
        // Update content
        previewName.textContent = name;
        previewDescription.textContent = description;
        
        // Sync hex input
        colorHexInput.value = color;
    }

    // Event listeners
    colorInput.addEventListener('input', updatePreview);
    
    colorHexInput.addEventListener('input', function() {
        if (this.value.match(/^#[0-9A-Fa-f]{6}$/)) {
            colorInput.value = this.value;
            updatePreview();
        }
    });

    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);

    // Form reset handler
    document.querySelector('button[type="reset"]').addEventListener('click', function() {
        setTimeout(updatePreview, 100);
    });

    // Initial preview update
    updatePreview();
});
</script>

<style>
.color-picker-group .form-control-color {
    height: 45px;
    min-width: 50px;
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.color-picker-group .color-hex-input {
    border-radius: 0;
}

.color-picker-group .input-group-text {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
    background: #f8f9fa;
    border-color: #dee2e6;
}

.preview-category {
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.preview-badge {
    font-size: 0.875rem;
    padding: 0.375rem 0.75rem;
    transition: all 0.3s ease;
}

.form-check-input:checked {
    background-color: #2c5aa0;
    border-color: #2c5aa0;
}

.card-header.bg-light {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
}

.preview-section {
    border-top: 1px solid #dee2e6;
    padding-top: 1rem;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.25rem 0;
}

.info-item:not(:last-child) {
    border-bottom: 1px solid #f8f9fa;
}
</style>
@endsection