@extends('layouts.app')

@section('title', 'Tin Tức - Hệ Thống Giáo Dục Khai Trí')

@section('content')
<!-- Hero Section -->
<section class="hero-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-5 fw-bold mb-3">Tin Tức & Sự Kiện</h1>
                <p class="lead mb-4">Cập nhật những thông tin mới nhất về giáo dục, đào tạo và các hoạt động tại Khai Trí</p>
                
                <!-- Search and Filter -->
                <div class="row g-3 justify-content-center">
                    <div class="col-md-8">
                        <form action="{{ route('news.index') }}" method="GET" class="search-form">
                            <div class="input-group shadow-sm">
                                <input type="text" name="search" class="form-control border-primary" 
                                       placeholder="Tìm kiếm tin tức..." value="{{ request('search') }}"
                                       style="border-radius: 50px 0 0 50px;">
                                <button class="btn btn-primary px-4" type="submit" style="border-radius: 0 50px 50px 0;">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form action="{{ route('news.index') }}" method="GET" id="sortForm">
                            <select class="form-select shadow-sm border-primary" onchange="document.getElementById('sortForm').submit()" name="sort">
                                <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>📰 Mới nhất</option>
                                <option value="popular" {{ request('sort') == 'popular' ? 'selected' : '' }}>🔥 Xem nhiều</option>
                                <option value="featured" {{ request('sort') == 'featured' ? 'selected' : '' }}>⭐ Nổi bật</option>
                            </select>
                        </form>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row justify-content-center mt-4 text-white">
                    <div class="col-auto">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-newspaper me-2"></i>
                            <span>{{ $posts->total() }} bài viết</span>
                        </div>
                    </div>
                    <div class="col-auto">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-eye me-2"></i>
                            <span>
                                @php
                                    $totalViews = 0;
                                    foreach($posts as $post) {
                                        $totalViews += $post->view_count;
                                    }
                                    // Cộng thêm lượt xem từ featured posts (tránh trùng lặp)
                                    foreach($featuredPosts as $post) {
                                        if(!$posts->contains('id', $post->id)) {
                                            $totalViews += $post->view_count;
                                        }
                                    }
                                    echo number_format($totalViews);
                                @endphp lượt xem
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Main Content -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <!-- Featured Posts -->
            @if($featuredPosts->count() > 0)
            <div class="col-12 mb-5">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h2 class="fw-bold mb-0">
                        <i class="fas fa-star text-warning me-2"></i>Tin Nổi Bật
                    </h2>
                    <a href="{{ route('news.index', ['sort' => 'featured']) }}" class="btn btn-outline-primary btn-sm">
                        Xem tất cả <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="row g-4">
                    @foreach($featuredPosts as $post)
                    <div class="col-lg-4 col-md-6">
                        <div class="card course-card h-100 shadow-sm">
                            <div class="course-image position-relative">
                                @if($post->featured_image)
                                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" 
                                         class="img-fluid w-100 h-100" style="object-fit: cover; height: 200px;">
                                @else
                                    <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-primary text-white" style="height: 200px;">
                                        <i class="fas fa-graduation-cap fa-3x"></i>
                                    </div>
                                @endif
                                <div class="course-badge bg-warning text-dark">
                                    <i class="fas fa-star me-1"></i>Nổi bật
                                </div>
                                <div class="position-absolute top-0 start-0 m-3">
                                    <span class="badge bg-white text-primary">{{ $post->category->name ?? 'Tin tức' }}</span>
                                </div>
                            </div>
                            <div class="card-body p-4 d-flex flex-column h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <small class="text-muted">
                                        @if($post->published_at)
                                            {{ $post->published_at->format('d/m/Y') }}
                                        @else
                                            {{ $post->created_at->format('d/m/Y') }}
                                        @endif
                                    </small>
                                </div>
                                <h5 class="card-title fw-bold text-dark mb-3">{{ Str::limit($post->title, 60) }}</h5>
                                <p class="card-text text-muted flex-grow-1">{{ Str::limit($post->excerpt ?? $post->content, 100) }}</p>
                                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                    <div class="d-flex gap-3 text-muted small">
                                        <span>
                                            <i class="fas fa-eye me-1"></i>{{ $post->view_count }}
                                        </span>
                                        <span>
                                            <i class="fas fa-user me-1"></i>{{ $post->author->name ?? 'Admin' }}
                                        </span>
                                    </div>
                                    <a href="{{ route('news.show', $post->slug) }}" class="btn btn-primary btn-sm">
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

            <!-- Main Content Area -->
            <div class="col-lg-8">
                <div class="d-flex align-items-center justify-content-between mb-4">
                    <h2 class="fw-bold mb-0">
                        <i class="fas fa-newspaper text-primary me-2"></i>Tất Cả Tin Tức
                    </h2>
                    <span class="text-muted small">{{ $posts->total() }} kết quả</span>
                </div>
                
                @if($posts->count() > 0)
                <div class="row g-4">
                    @foreach($posts as $post)
                    <div class="col-12">
                        <div class="card course-card h-100 shadow-sm">
                            <div class="row g-0">
                                <div class="col-md-4">
                                    <div class="course-image h-100 position-relative">
                                        @if($post->featured_image)
                                            <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" 
                                                 class="img-fluid w-100 h-100" style="object-fit: cover;">
                                        @else
                                            <div class="d-flex align-items-center justify-content-center w-100 h-100 bg-primary text-white">
                                                <i class="fas fa-graduation-cap fa-3x"></i>
                                            </div>
                                        @endif
                                        <div class="position-absolute top-0 start-0 m-3">
                                            <span class="badge bg-white text-primary">{{ $post->category->name ?? 'Tin tức' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="card-body p-4 d-flex flex-column h-100">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="card-title fw-bold text-dark mb-1">{{ Str::limit($post->title, 70) }}</h5>
                                            <small class="text-muted ms-2 flex-shrink-0">
                                                @if($post->published_at)
                                                    {{ $post->published_at->format('d/m/Y') }}
                                                @else
                                                    {{ $post->created_at->format('d/m/Y') }}
                                                @endif
                                            </small>
                                        </div>
                                        
                                        <p class="card-text text-muted flex-grow-1">{{ Str::limit($post->excerpt ?? $post->content, 150) }}</p>
                                        
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                            <div class="d-flex gap-3 text-muted small">
                                                <span>
                                                    <i class="fas fa-eye me-1"></i>{{ $post->view_count }} lượt xem
                                                </span>
                                                <span>
                                                    <i class="fas fa-user me-1"></i>{{ $post->author->name ?? 'Admin' }}
                                                </span>
                                            </div>
                                            <a href="{{ route('news.show', $post->slug) }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-book-open me-1"></i>Đọc tiếp
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-5">
                    {{ $posts->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <div class="empty-state">
                        <i class="fas fa-newspaper fa-4x text-muted mb-4"></i>
                        <h4 class="text-muted mb-3">Không tìm thấy tin tức nào</h4>
                        <p class="text-muted mb-4">Hãy thử tìm kiếm với từ khóa khác hoặc quay lại sau.</p>
                        <div class="d-flex gap-3 justify-content-center">
                            <a href="{{ route('news.index') }}" class="btn btn-primary">
                                <i class="fas fa-sync me-2"></i>Xem tất cả
                            </a>
                            <a href="{{ url('/') }}" class="btn btn-outline-primary">
                                <i class="fas fa-home me-2"></i>Về trang chủ
                            </a>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Categories -->
                <div class="card feature-card mb-4 shadow-sm">
                    <div class="card-header bg-primary text-white py-3">
                        <h5 class="fw-bold mb-0"><i class="fas fa-folder me-2"></i>Danh Mục</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            @foreach($categories as $category)
                            <a href="{{ route('news.category', $category->slug) }}" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-folder me-2 text-primary"></i>
                                    <span>{{ $category->name }}</span>
                                </div>
                                <span class="badge bg-primary rounded-pill">
                                    @php
                                        $postCount = \App\Models\Post::published()
                                            ->where('category_id', $category->id)
                                            ->count();
                                    @endphp
                                    {{ $postCount }}
                                </span>
                            </a>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Popular Posts -->
                <div class="card feature-card shadow-sm">
                    <div class="card-header bg-warning text-dark py-3">
                        <h5 class="fw-bold mb-0"><i class="fas fa-fire me-2"></i>Tin Xem Nhiều</h5>
                    </div>
                    <div class="card-body p-3">
                        @foreach($popularPosts as $index => $post)
                        <div class="d-flex align-items-start mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="flex-shrink-0 me-3">
                                <div class="position-relative">
                                    @if($post->featured_image)
                                    <img src="{{ $post->featured_image_url }}" alt="{{ $post->title }}" 
                                         class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                    @else
                                    <div class="rounded d-flex align-items-center justify-content-center bg-primary text-white" 
                                         style="width: 60px; height: 60px;">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    @endif
                                    @if($index < 3)
                                    <span class="position-absolute top-0 start-100 translate-middle badge bg-danger rounded-pill">
                                        {{ $index + 1 }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">
                                    <a href="{{ route('news.show', $post->slug) }}" 
                                       class="text-decoration-none text-dark hover-text-primary">
                                        {{ Str::limit($post->title, 50) }}
                                    </a>
                                </h6>
                                <div class="d-flex align-items-center text-muted small">
                                    <span class="me-2">
                                        <i class="fas fa-eye me-1"></i>{{ $post->view_count }}
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar me-1"></i>
                                        @if($post->published_at)
                                            {{ $post->published_at->format('d/m') }}
                                        @else
                                            {{ $post->created_at->format('d/m') }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection