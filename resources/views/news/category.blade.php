@extends('layouts.app')

@section('title', $category->name . ' - Tin Tức Khai Trí')

@section('content')
<!-- Hero Section -->
<section class="hero-section py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center">
                <h1 class="display-5 fw-bold mb-3">{{ $category->name }}</h1>
                <p class="lead mb-4">{{ $category->description ?? 'Các tin tức thuộc danh mục ' . $category->name }}</p>
                
                <!-- Category Stats -->
                <div class="row justify-content-center mt-4">
                    <div class="col-auto">
                        <div class="d-flex align-items-center text-white">
                            <i class="fas fa-newspaper me-2"></i>
                            <span>{{ $posts->total() }} bài viết</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Category Posts -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row">
            <div class="mb-4">
                <a href="{{ url()->previous() }}" class="btn btn-outline-primary btn-sm">
                   <i class="fas fa-arrow-left me-2"></i>Quay lại
                </a>
            </div>
            <!-- Main Content -->
            <div class="col-lg-8">
                @if($posts->count() > 0)
                <!-- Sort Options -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold mb-0">Tất cả bài viết</h4>
                    <div class="d-flex gap-2">
                        <span class="text-muted">Sắp xếp:</span>
                        <select class="form-select form-select-sm" style="width: auto;" onchange="window.location.href = this.value">
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'latest']) }}" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>Mới nhất</option>
                            <option value="{{ request()->fullUrlWithQuery(['sort' => 'popular']) }}" {{ request('sort') == 'popular' ? 'selected' : '' }}>Xem nhiều</option>
                        </select>
                    </div>
                </div>

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
                        <h4 class="text-muted mb-3">Chưa có tin tức trong danh mục này</h4>
                        <p class="text-muted mb-4">Hãy quay lại sau để xem các bài viết mới.</p>
                        <div class="d-flex gap-3 justify-content-center">
                            <a href="{{ route('news.index') }}" class="btn btn-primary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại trang tin tức
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
                            @foreach($categories as $cat)
                            <a href="{{ route('news.category', $cat->slug) }}" 
                               class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 
                                      {{ $cat->id == $category->id ? 'active' : '' }}">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-folder me-2 {{ $cat->id == $category->id ? 'text-white' : 'text-primary' }}"></i>
                                    <span>{{ $cat->name }}</span>
                                </div>
                                <span class="badge bg-primary rounded-pill">
                                    @php
                                        $postCount = \App\Models\Post::published()
                                            ->where('category_id', $cat->id)
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
                        @php
                            $popularPosts = \App\Models\Post::published()
                                ->orderBy('view_count', 'desc')
                                ->limit(5)
                                ->get();
                        @endphp
                        
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
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">
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

                <!-- Quick Actions -->
                <div class="card feature-card mt-4 shadow-sm">
                    <div class="card-body p-4 text-center">
                        <h6 class="fw-bold mb-3">Hỗ trợ nhanh</h6>
                        <div class="d-grid gap-2">
                            <a href="tel:02812345678" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-phone me-2"></i>Gọi tư vấn
                            </a>
                            <a href="{{ route('courses.index') }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-book me-2"></i>Khóa học
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection