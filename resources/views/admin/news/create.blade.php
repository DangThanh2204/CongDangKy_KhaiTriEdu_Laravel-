@extends('layouts.admin')

@section('title', 'Thêm bài viết mới')
@section('page-title', 'Thêm bài viết mới')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Thông tin bài viết</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.news.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="title" class="form-label">Tiêu đề bài viết <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('title') is-invalid @enderror" 
                               id="title" 
                               name="title" 
                               value="{{ old('title') }}" 
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
                                  required>{{ old('excerpt') }}</textarea>
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
                                  required>{{ old('content') }}</textarea>
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
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
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
                                    <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                                    <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Xuất bản ngay</option>
                                </select>
                                @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="featured_image" class="form-label">Ảnh đại diện</label>
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
                                   {{ old('is_featured') ? 'checked' : '' }}>
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
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Lưu bài viết
                            </button>
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
                           value="{{ old('meta_title') }}"
                           placeholder="Tiêu đề SEO (tối đa 60 ký tự)">
                    <div class="form-text">Để trống sẽ sử dụng tiêu đề bài viết</div>
                </div>
                <div class="mb-0">
                    <label for="meta_description" class="form-label">Meta Description</label>
                    <textarea class="form-control" 
                              id="meta_description" 
                              name="meta_description" 
                              rows="3"
                              placeholder="Mô tả SEO (tối đa 160 ký tự)">{{ old('meta_description') }}</textarea>
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
                           value="{{ old('published_at') }}">
                    <div class="form-text">Để trống sẽ xuất bản ngay lập tức</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto generate slug from title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    
    if (titleInput && slugInput) {
        titleInput.addEventListener('blur', function() {
            if (!slugInput.value) {
                const slug = this.value
                    .toLowerCase()
                    .replace(/[^a-z0-9 -]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
                slugInput.value = slug;
            }
        });
    }
});
</script>
@endsection