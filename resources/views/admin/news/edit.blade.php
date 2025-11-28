@extends('layouts.admin')

@section('title', 'Chỉnh sửa bài viết')
@section('page-title', 'Chỉnh sửa bài viết')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Chỉnh sửa bài viết</h5>
                    <button type="button" id="previewBtn" class="btn btn-outline-primary">
                        <i class="fas fa-eye me-2"></i>Xem trước
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.news.update', $news->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-4">
                        <label for="title" class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title', $news->title) }}" 
                               placeholder="Nhập tiêu đề bài viết..." 
                               required>
                        @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="excerpt" class="form-label">Mô tả ngắn <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('excerpt') is-invalid @enderror" 
                                  id="excerpt" 
                                  name="excerpt" 
                                  rows="3" 
                                  placeholder="Mô tả ngắn về bài viết..." 
                                  required>{{ old('excerpt', $news->excerpt) }}</textarea>
                        @error('excerpt')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="content" class="form-label">Nội dung <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('content') is-invalid @enderror" 
                                  id="content" 
                                  name="content" 
                                  rows="15" 
                                  placeholder="Nội dung bài viết..." 
                                  required>{{ old('content', $news->content) }}</textarea>
                        @error('content')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="category_id" class="form-label">Danh mục <span class="text-danger">*</span></label>
                                <select class="form-select @error('category_id') is-invalid @enderror" 
                                        id="category_id" 
                                        name="category_id" 
                                        required>
                                    <option value="">Chọn danh mục</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $news->category_id) == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-4">
                                <label for="status" class="form-label">Trạng thái <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" 
                                        id="status" 
                                        name="status" 
                                        required>
                                    <option value="draft" {{ old('status', $news->status) == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                                    <option value="published" {{ old('status', $news->status) == 'published' ? 'selected' : '' }}>Xuất bản</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="featured_image" class="form-label">Ảnh đại diện</label>
                        @if($news->featured_image_url)
                        <div class="mb-3">
                            <img src="{{ $news->featured_image_url }}" 
                                 alt="{{ $news->title }}" 
                                 class="img-fluid rounded mb-2"
                                 style="max-height: 200px;">
                            <div class="form-text">Ảnh hiện tại</div>
                        </div>
                        @endif
                        <input type="file" 
                               class="form-control @error('featured_image') is-invalid @enderror" 
                               id="featured_image" 
                               name="featured_image"
                               accept="image/*">
                        @error('featured_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Định dạng: JPG, PNG, GIF. Kích thước tối đa: 2MB</div>
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   id="is_featured" 
                                   name="is_featured" 
                                   value="1"
                                   {{ (old('is_featured', $news->meta['is_featured'] ?? false)) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_featured">
                                Đánh dấu là bài viết nổi bật
                            </label>
                        </div>
                    </div>

                    <div class="border-top pt-4 mt-4">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại
                            </a>
                            <div>
                                <a href="{{ route('admin.news.preview', $news->id) }}" class="btn btn-outline-info me-2" target="_blank">
                                    <i class="fas fa-external-link-alt me-2"></i>Xem trước đầy đủ
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Cập nhật bài viết
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- SEO Settings -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-search me-2"></i>Cài đặt SEO</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="meta_title" class="form-label">Meta Title</label>
                    <input type="text" 
                           class="form-control" 
                           id="meta_title" 
                           name="meta_title" 
                           value="{{ old('meta_title', $news->meta['title'] ?? '') }}"
                           placeholder="Tiêu đề SEO (tối đa 60 ký tự)">
                    <div class="form-text">Để trống sẽ sử dụng tiêu đề bài viết</div>
                </div>
                <div class="mb-0">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea class="form-control" 
                              id="meta_description" 
                              name="meta_description" 
                              rows="3"
                              placeholder="Mô tả SEO (tối đa 160 ký tự)">{{ old('meta_description', $news->meta['description'] ?? '') }}</textarea>
                    <div class="form-text">Để trống sẽ sử dụng mô tả ngắn</div>
                </div>
            </div>
        </div>

        <!-- Publishing Options -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Tùy chọn xuất bản</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <label for="published_at" class="form-label">Lên lịch xuất bản</label>
                    <input type="datetime-local" 
                           class="form-control" 
                           id="published_at" 
                           name="published_at" 
                           value="{{ old('published_at', $news->published_at ? $news->published_at->format('Y-m-d\TH:i') : '') }}">
                    <div class="form-text">Để trống sẽ xuất bản ngay lập tức</div>
                </div>
            </div>
        </div>

        <!-- Quick Preview -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-eye me-2"></i>Xem trước nhanh</h6>
            </div>
            <div class="card-body">
                <div id="quickPreview" class="border rounded p-3 bg-light" style="min-height: 200px;">
                    <h6 id="previewTitle" class="fw-bold">{{ $news->title }}</h6>
                    <p id="previewExcerpt" class="text-muted small mb-2">{{ Str::limit($news->excerpt, 100) }}</p>
                    <div id="previewContent" class="small text-muted">
                        {{ Str::limit(strip_tags($news->content), 150) }}
                    </div>
                </div>
                <div class="mt-3">
                    <button type="button" id="updatePreviewBtn" class="btn btn-sm btn-outline-primary w-100">
                        <i class="fas fa-sync me-1"></i>Cập nhật xem trước
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">Xem trước bài viết</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Article Header -->
                <div class="text-center mb-4">
                    <div id="modalPreviewImage">
                        @if($news->featured_image_url)
                        <img src="{{ $news->featured_image_url }}" 
                             alt="{{ $news->title }}" 
                             class="img-fluid rounded mb-3"
                             style="max-height: 300px; object-fit: cover;">
                        @endif
                    </div>
                    
                    <h2 id="modalPreviewTitle" class="fw-bold mb-3">{{ $news->title }}</h2>
                    
                    <div class="d-flex justify-content-center align-items-center flex-wrap gap-3 mb-3">
                        <span id="modalPreviewCategory" class="badge bg-success">
                            {{ $news->category->name }}
                        </span>
                        <span class="text-muted">
                            <i class="fas fa-user me-1"></i>{{ $news->author->fullname }}
                        </span>
                        <span class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            {{ $news->published_at ? $news->published_at->format('d/m/Y H:i') : 'Chưa xuất bản' }}
                        </span>
                    </div>
                    
                    <div id="modalPreviewExcerpt" class="lead text-muted">
                        {{ $news->excerpt }}
                    </div>
                </div>

                <!-- Article Content -->
                <div class="article-content">
                    <div id="modalPreviewContent" class="content-body" style="line-height: 1.7;">
                        {!! nl2br(e($news->content)) !!}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <a href="{{ route('admin.news.preview', $news->id) }}" class="btn btn-primary" target="_blank">
                    <i class="fas fa-external-link-alt me-2"></i>Xem trang đầy đủ
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const previewBtn = document.getElementById('previewBtn');
    const updatePreviewBtn = document.getElementById('updatePreviewBtn');
    const previewModal = new bootstrap.Modal(document.getElementById('previewModal'));
    
    // Form elements
    const titleInput = document.getElementById('title');
    const excerptInput = document.getElementById('excerpt');
    const contentInput = document.getElementById('content');
    const categorySelect = document.getElementById('category_id');
    const featuredImageInput = document.getElementById('featured_image');
    
    // Quick preview elements
    const quickPreviewTitle = document.getElementById('previewTitle');
    const quickPreviewExcerpt = document.getElementById('previewExcerpt');
    const quickPreviewContent = document.getElementById('previewContent');
    
    // Modal preview elements
    const modalPreviewTitle = document.getElementById('modalPreviewTitle');
    const modalPreviewExcerpt = document.getElementById('modalPreviewExcerpt');
    const modalPreviewContent = document.getElementById('modalPreviewContent');
    const modalPreviewCategory = document.getElementById('modalPreviewCategory');
    const modalPreviewImage = document.getElementById('modalPreviewImage');
    
    // Get category name from option text
    function getCategoryName(categoryId) {
        const option = categorySelect.querySelector(`option[value="${categoryId}"]`);
        return option ? option.textContent : 'Chưa chọn danh mục';
    }
    
    // Update quick preview
    function updateQuickPreview() {
        quickPreviewTitle.textContent = titleInput.value || 'Tiêu đề bài viết';
        quickPreviewExcerpt.textContent = excerptInput.value ? Str.limit(excerptInput.value, 100) : 'Mô tả ngắn về bài viết';
        quickPreviewContent.textContent = contentInput.value ? Str.limit(stripTags(contentInput.value), 150) : 'Nội dung bài viết';
    }
    
    // Update modal preview
    function updateModalPreview() {
        modalPreviewTitle.textContent = titleInput.value || 'Tiêu đề bài viết';
        modalPreviewExcerpt.textContent = excerptInput.value || 'Mô tả ngắn về bài viết';
        modalPreviewContent.innerHTML = contentInput.value ? contentInput.value.replace(/\n/g, '<br>') : 'Nội dung bài viết';
        modalPreviewCategory.textContent = getCategoryName(categorySelect.value);
        
        // Handle featured image preview
        if (featuredImageInput.files && featuredImageInput.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                modalPreviewImage.innerHTML = `<img src="${e.target.result}" alt="${titleInput.value}" class="img-fluid rounded mb-3" style="max-height: 300px; object-fit: cover;">`;
            };
            reader.readAsDataURL(featuredImageInput.files[0]);
        }
    }
    
    // Helper function to strip HTML tags
    function stripTags(html) {
        const tmp = document.createElement('DIV');
        tmp.innerHTML = html;
        return tmp.textContent || tmp.innerText || '';
    }
    
    // Helper function to limit string length
    const Str = {
        limit: function(text, length) {
            return text.length > length ? text.substring(0, length) + '...' : text;
        }
    };
    
    // Event listeners
    previewBtn.addEventListener('click', function() {
        updateModalPreview();
        previewModal.show();
    });
    
    updatePreviewBtn.addEventListener('click', function() {
        updateQuickPreview();
    });
    
    // Auto-update quick preview when form changes
    [titleInput, excerptInput, contentInput].forEach(input => {
        input.addEventListener('input', updateQuickPreview);
    });
    
    // Initialize quick preview
    updateQuickPreview();
});
</script>
@endsection