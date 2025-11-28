@extends('layouts.admin')

@section('title', 'Xem trước bài viết')
@section('page-title', 'Xem trước bài viết')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Xem trước bài viết</h5>
                    <div>
                        <a href="{{ route('admin.news.edit', $news->id) }}" class="btn btn-warning me-2">
                            <i class="fas fa-edit me-2"></i>Chỉnh sửa
                        </a>
                        <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Quay lại
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Article Header -->
                <div class="text-center mb-5">
                    @if($news->featured_image_url)
                    <div class="mb-4">
                        <img src="{{ $news->featured_image_url }}" 
                             alt="{{ $news->title }}" 
                             class="img-fluid rounded"
                             style="max-height: 400px; object-fit: cover;">
                    </div>
                    @endif
                    
                    <h1 class="display-5 fw-bold mb-3">{{ $news->title }}</h1>
                    
                    <div class="d-flex justify-content-center align-items-center flex-wrap gap-3 mb-3">
                        @if($news->meta['is_featured'] ?? false)
                        <span class="badge bg-primary">
                            <i class="fas fa-star me-1"></i>Nổi bật
                        </span>
                        @endif
                        
                        <span class="badge bg-success">
                            {{ $news->category->name }}
                        </span>
                        
                        <span class="text-muted">
                            <i class="fas fa-user me-1"></i>{{ $news->author->fullname }}
                        </span>
                        
                        <span class="text-muted">
                            <i class="fas fa-calendar me-1"></i>
                            {{ $news->published_at ? $news->published_at->format('d/m/Y H:i') : 'Chưa xuất bản' }}
                        </span>
                        
                        <span class="text-muted">
                            <i class="fas fa-eye me-1"></i>{{ $news->view_count }} lượt xem
                        </span>
                    </div>
                    
                    @if($news->excerpt)
                    <div class="lead text-muted mb-4">
                        {{ $news->excerpt }}
                    </div>
                    @endif
                </div>

                <!-- Article Content -->
                <div class="article-content mb-5">
                    <div class="content-body" style="font-size: 1.1rem; line-height: 1.8;">
                        {!! nl2br(e($news->content)) !!}
                    </div>
                </div>

                <!-- Article Meta -->
                <div class="border-top pt-4 mt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-tags me-2"></i>Thông tin bài viết</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-2">
                                        <small class="text-muted">Trạng thái:</small>
                                        <div>
                                            @if($news->status === 'published')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check me-1"></i>Đã xuất bản
                                            </span>
                                            @else
                                            <span class="badge bg-warning text-dark">
                                                <i class="fas fa-pencil-alt me-1"></i>Bản nháp
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Slug:</small>
                                        <div class="fw-semibold">{{ $news->slug }}</div>
                                    </div>
                                    <div class="mb-2">
                                        <small class="text-muted">Danh mục:</small>
                                        <div class="fw-semibold">{{ $news->category->name }}</div>
                                    </div>
                                    <div class="mb-0">
                                        <small class="text-muted">Tác giả:</small>
                                        <div class="fw-semibold">{{ $news->author->fullname }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0"><i class="fas fa-search me-2"></i>Thông tin SEO</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <small class="text-muted">Meta Title:</small>
                                        <div class="fw-semibold">{{ $news->meta['title'] ?? $news->title }}</div>
                                    </div>
                                    <div class="mb-0">
                                        <small class="text-muted">Meta Description:</small>
                                        <div class="fw-semibold">{{ $news->meta['description'] ?? $news->excerpt }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="border-top pt-4 mt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                Tạo lúc: {{ $news->created_at->format('d/m/Y H:i') }} | 
                                Cập nhật: {{ $news->updated_at->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <div class="btn-group">
                            <form action="{{ route('admin.news.toggle-featured', $news->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-info">
                                    <i class="fas fa-star me-2"></i>
                                    {{ ($news->meta['is_featured'] ?? false) ? 'Bỏ nổi bật' : 'Đánh dấu nổi bật' }}
                                </button>
                            </form>
                            
                            @if($news->status === 'draft')
                            <a href="{{ route('admin.news.edit', $news->id) }}?status=published" 
                               class="btn btn-success">
                                <i class="fas fa-paper-plane me-2"></i>Xuất bản ngay
                            </a>
                            @endif
                            
                            <a href="{{ route('admin.news.edit', $news->id) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Chỉnh sửa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection