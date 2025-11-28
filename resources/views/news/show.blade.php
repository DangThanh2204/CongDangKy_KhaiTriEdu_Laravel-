@extends('layouts.app')

@section('title', $post->title . ' - Hệ Thống Giáo Dục Khai Trí')

@section('content')
<!-- Article Content -->
<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Back Button -->
                <div class="mb-4">
                    <a href="{{ url()->previous() }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại
                    </a>
                </div>

                <article class="card feature-card">
                    <div class="card-body p-4">
                        <!-- Article Header -->
                        <header class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary">{{ $post->category->name ?? 'Tin tức' }}</span>
                                <small class="text-muted">
                                    @if($post->published_at)
                                        {{ $post->published_at->format('d/m/Y H:i') }}
                                    @else
                                        {{ $post->created_at->format('d/m/Y H:i') }}
                                    @endif
                                </small>
                            </div>
                            
                            <h1 class="fw-bold mb-3">{{ $post->title }}</h1>
                            
                            <div class="d-flex align-items-center text-muted mb-4">
                                <small class="me-3">
                                    <i class="fas fa-user me-1"></i>{{ $post->author->name ?? 'Admin' }}
                                </small>
                                <small class="me-3">
                                    <i class="fas fa-eye me-1"></i>{{ $post->view_count }} lượt xem
                                </small>
                                @if($post->is_featured)
                                <small class="text-warning">
                                    <i class="fas fa-star me-1"></i>Nổi bật
                                </small>
                                @endif
                            </div>
                        </header>

                        <!-- Featured Image -->
                        @if($post->featured_image)
                        <div class="mb-4">
                            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" 
                                 class="img-fluid rounded w-100" style="max-height: 400px; object-fit: cover;">
                        </div>
                        @else
                        <div class="mb-4">
                            <div class="d-flex align-items-center justify-content-center rounded w-100" style="height: 300px; background: var(--gradient-primary);">
                                <i class="fas fa-graduation-cap fa-5x text-white"></i>
                            </div>
                        </div>
                        @endif

                        <!-- Article Content -->
                        <div class="article-content">
                            {!! $post->content !!}
                        </div>

                        <!-- Share Buttons -->
                        <div class="mt-4 pt-4 border-top">
                            <h6 class="fw-bold mb-3">Chia sẻ bài viết:</h6>
                            <div class="social-links">
                                <a href="#" class="facebook" title="Chia sẻ lên Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="youtube" title="Chia sẻ lên Zalo">
                                    <i class="fab fa-facebook-messenger"></i>
                                </a>
                                <a href="#" class="tiktok" title="Sao chép liên kết">
                                    <i class="fas fa-link"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- Related Posts -->
                @if($relatedPosts->count() > 0)
                <div class="mt-5">
                    <h3 class="fw-bold mb-4">Tin Liên Quan</h3>
                    <div class="row g-4">
                        @foreach($relatedPosts as $relatedPost)
                        <div class="col-md-6">
                            <div class="card course-card h-100">
                                <div class="course-image" style="height: 180px;">
                                    @if($relatedPost->featured_image)
                                        <img src="{{ $relatedPost->featured_image_url }}" alt="{{ $relatedPost->title }}" class="img-fluid w-100 h-100" style="object-fit: cover;">
                                    @else
                                        <div class="d-flex align-items-center justify-content-center w-100 h-100">
                                            <i class="fas fa-graduation-cap fa-2x text-white"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-primary">{{ $relatedPost->category->name ?? 'Tin tức' }}</span>
                                        <small class="text-muted">
                                            @if($relatedPost->published_at)
                                                {{ $relatedPost->published_at->format('d/m/Y') }}
                                            @else
                                                {{ $relatedPost->created_at->format('d/m/Y') }}
                                            @endif
                                        </small>
                                    </div>
                                    <h6 class="card-title fw-bold">{{ Str::limit($relatedPost->title, 60) }}</h6>
                                    <div class="d-grid mt-3">
                                        <a href="{{ route('news.show', $relatedPost->slug) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-book-open me-1"></i>Đọc tiếp
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection