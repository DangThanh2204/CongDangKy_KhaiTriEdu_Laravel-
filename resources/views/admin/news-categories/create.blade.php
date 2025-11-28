@extends('layouts.admin')

@section('title', 'Thêm Danh mục Tin tức')
@section('page-title', 'Thêm Danh mục Tin tức')

@section('content')
<div class="admin-content">
    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                <i class="fas fa-folder-plus me-2"></i>Thêm danh mục mới
            </h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.news-categories.store') }}" method="POST">
                @csrf
                
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
                                           id="name" name="name" value="{{ old('name') }}" 
                                           placeholder="Nhập tên danh mục (ví dụ: Tin tức, Sự kiện, Thông báo...)" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Tên danh mục sẽ hiển thị trên website và trong quản trị.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">
                                        <strong>Mô tả</strong>
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" name="description" rows="3" 
                                              placeholder="Nhập mô tả ngắn về danh mục (tối đa 500 ký tự)">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Mô tả giúp người dùng hiểu rõ hơn về nội dung của danh mục.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Settings Card -->
                        <div class="card mb-4">
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
                                                       id="color" name="color" value="{{ old('color', '#2c5aa0') }}" 
                                                       title="Chọn màu sắc cho danh mục">
                                                <input type="text" class="form-control color-hex-input @error('color') is-invalid @enderror" 
                                                       id="color_hex" name="color_hex" value="{{ old('color', '#2c5aa0') }}" 
                                                       pattern="^#[0-9A-Fa-f]{6}$" 
                                                       placeholder="#2c5aa0" maxlength="7">
                                                <span class="input-group-text">
                                                    <i class="fas fa-palette"></i>
                                                </span>
                                            </div>
                                            @error('color')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Màu sắc đại diện cho danh mục, sử dụng định dạng HEX (#RRGGBB).</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="order" class="form-label">
                                                <strong>Thứ tự hiển thị</strong>
                                            </label>
                                            <input type="number" class="form-control @error('order') is-invalid @enderror" 
                                                   id="order" name="order" value="{{ old('order', 0) }}" 
                                                   min="0" max="999" step="1">
                                            @error('order')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="form-text">Số nhỏ hơn sẽ hiển thị trước. Mặc định: 0.</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Icon selection -->
                                <div class="mb-3">
                                    <label for="icon" class="form-label">
                                        <strong>Icon</strong>
                                    </label>
                                    <select class="form-select @error('icon') is-invalid @enderror" 
                                            id="icon" name="icon">
                                        <option value="">-- Chọn icon --</option>
                                        <option value="fas fa-code" {{ old('icon') == 'fas fa-code' ? 'selected' : '' }}>Code</option>
                                        <option value="fas fa-paint-brush" {{ old('icon') == 'fas fa-paint-brush' ? 'selected' : '' }}>Paint Brush</option>
                                        <option value="fas fa-book" {{ old('icon') == 'fas fa-book' ? 'selected' : '' }}>Book</option>
                                        <option value="fas fa-folder" {{ old('icon') == 'fas fa-folder' ? 'selected' : '' }}>Folder</option>
                                        <option value="fas fa-bolt" {{ old('icon') == 'fas fa-bolt' ? 'selected' : '' }}>Bolt</option>
                                    </select>
                                    @error('icon')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Chọn icon đại diện cho danh mục. Sẽ hiển thị trên website và trong quản trị.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Status & Preview Card -->
                        <div class="card">
                            <div class="card-header bg-light">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-eye me-2"></i>Trạng thái & Xem trước
                                </h6>
                            </div>
                            <div class="card-body">
                                <!-- Status Settings -->
                                <div class="mb-4">
                                    <label class="form-label"><strong>Trạng thái</strong></label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" 
                                               id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium" for="is_active">
                                            Kích hoạt danh mục
                                        </label>
                                    </div>
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>
                                        Danh mục đã kích hoạt sẽ hiển thị trên website và có thể được chọn khi đăng bài.
                                    </div>
                                </div>

                                <!-- Preview Section -->
                                <div class="preview-section">
                                    <label class="form-label"><strong>Xem trước</strong></label>
                                    <div class="preview-category p-3 rounded border d-flex align-items-center">
                                        <span class="badge me-2 preview-badge" 
                                              style="background: {{ old('color', '#2c5aa0') }}; color: white;">
                                            <i class="{{ old('icon') ?? '' }} me-1" style="display: {{ old('icon') ? 'inline-block' : 'none' }}"></i>
                                            <span class="preview-name">{{ old('name', 'Tên danh mục') }}</span>
                                        </span>
                                        <small class="text-muted preview-count">0 bài viết</small>
                                    </div>
                                    <p class="preview-description mb-0 small text-muted mt-1">
                                        {{ old('description', 'Mô tả danh mục sẽ hiển thị ở đây...') }}
                                    </p>
                                    <div class="form-text mt-2">
                                        <i class="fas fa-lightbulb me-1"></i>
                                        Đây là cách danh mục sẽ hiển thị trên website.
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
                            <a href="{{ route('admin.news-categories.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                            </a>
                            <div>
                                <button type="reset" class="btn btn-outline-danger me-2">
                                    <i class="fas fa-undo me-2"></i>Nhập lại
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Tạo danh mục
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
    const iconSelect = document.getElementById('icon');
    const previewBadge = document.querySelector('.preview-badge');
    const previewName = document.querySelector('.preview-name');
    const previewDescription = document.querySelector('.preview-description');
    const previewCategory = document.querySelector('.preview-category');
    const previewIcon = previewBadge.querySelector('i');

    function updatePreview() {
        const color = colorInput.value;
        const name = nameInput.value || 'Tên danh mục';
        const description = descriptionInput.value || 'Mô tả danh mục sẽ hiển thị ở đây...';
        const iconClass = iconSelect.value;

        // Update color
        previewBadge.style.backgroundColor = color;
        previewCategory.style.borderColor = color + '40';
        
        // Update content
        previewName.textContent = name;
        previewDescription.textContent = description;

        // Update icon
        if(iconClass){
            previewIcon.className = iconClass + ' me-1';
            previewIcon.style.display = 'inline-block';
        } else {
            previewIcon.style.display = 'none';
        }

        // Sync hex input
        colorHexInput.value = color;
    }

    colorInput.addEventListener('input', updatePreview);
    colorHexInput.addEventListener('input', function() {
        if(this.value.match(/^#[0-9A-Fa-f]{6}$/)){
            colorInput.value = this.value;
            updatePreview();
        }
    });
    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    iconSelect.addEventListener('change', updatePreview);

    document.querySelector('button[type="reset"]').addEventListener('click', function(){
        setTimeout(updatePreview, 100);
    });

    updatePreview();
});
</script>


@endsection
