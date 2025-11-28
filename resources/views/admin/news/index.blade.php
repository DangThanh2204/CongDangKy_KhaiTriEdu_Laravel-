
@extends('layouts.admin')

@section('title', 'Quản lý Tin tức')
@section('page-title', 'Quản lý Tin tức')

@section('content')
<!-- Header với thống kê -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Danh sách bài viết</h4>
                <p class="text-muted mb-0">Quản lý tất cả bài viết tin tức trên hệ thống</p>
            </div>
            <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Thêm bài viết
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid mb-4">
    <div class="stat-card users">
        <div class="stat-icon">
            <i class="fas fa-newspaper"></i>
        </div>
        <div class="stat-number">{{ $totalPosts }}</div>
        <div class="stat-label">Tổng bài viết</div>
    </div>
    
    <div class="stat-card courses">
        <div class="stat-icon">
            <i class="fas fa-eye"></i>
        </div>
        <div class="stat-number">{{ $publishedPosts }}</div>
        <div class="stat-label">Đã xuất bản</div>
    </div>
    
    <div class="stat-card revenue">
        <div class="stat-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="stat-number">{{ $draftPosts }}</div>
        <div class="stat-label">Bản nháp</div>
    </div>
    
    <div class="stat-card orders">
        <div class="stat-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-number">{{ $totalViews }}</div>
        <div class="stat-label">Lượt xem</div>
    </div>
</div>

<!-- Filters và Search -->
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('admin.news.index') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" placeholder="Tiêu đề bài viết..." value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Bản nháp</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Danh mục</label>
                <select name="category" class="form-select">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Lọc
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Posts Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Bài viết</th>
                        <th>Danh mục</th>
                        <th>Tác giả</th>
                        <th>Lượt xem</th>
                        <th>Trạng thái</th>
                        <th>Ngày đăng</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($posts as $post)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <img src="{{ $post->featured_image_url }}" 
                                     alt="{{ $post->title }}" 
                                     class="rounded me-3" 
                                     width="60" 
                                     height="40"
                                     style="object-fit: cover;">
                                <div>
                                    <h6 class="mb-1">{{ Str::limit($post->title, 60) }}</h6>
                                    <small class="text-muted">
                                        {{ Str::limit($post->excerpt, 80) }}
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge bg-primary">
                                {{ $post->category->name }}
                            </span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="{{ $post->author->avatar ? asset('storage/' . $post->author->avatar) : asset('images/default-avatar.png') }}" 
                                     alt="{{ $post->author->fullname }}" 
                                     class="rounded-circle me-2" 
                                     width="30" 
                                     height="30">
                                <span>{{ $post->author->fullname }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-muted">
                                <i class="fas fa-eye me-1"></i>{{ $post->view_count }}
                            </span>
                        </td>
                        <td>
                            @if($post->status === 'published')
                            <span class="badge bg-success">
                                <i class="fas fa-check me-1"></i>Đã xuất bản
                            </span>
                            @else
                            <span class="badge bg-warning text-dark">
                                <i class="fas fa-pencil-alt me-1"></i>Bản nháp
                            </span>
                            @endif
                            
                            @if($post->meta['is_featured'] ?? false)
                            <span class="badge bg-primary ms-1">
                                <i class="fas fa-star me-1"></i>Nổi bật
                            </span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ $post->published_at ? $post->published_at->format('d/m/Y') : 'Chưa đăng' }}
                            </small>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.news.preview', $post->id) }}" 
                                   class="btn btn-outline-primary"
                                   title="Xem trước">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                
                                <a href="{{ route('admin.news.edit', $post->id) }}" 
                                   class="btn btn-outline-warning"
                                   title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <form action="{{ route('admin.news.toggle-featured', $post->id) }}" 
                                      method="POST" 
                                      class="d-inline">
                                    @csrf
                                    <button type="submit" 
                                            class="btn btn-outline-info"
                                            title="{{ ($post->meta['is_featured'] ?? false) ? 'Bỏ nổi bật' : 'Đánh dấu nổi bật' }}">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.news.destroy', $post->id) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" title="Xóa">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5">
                            <i class="fas fa-newspaper fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có bài viết nào</h5>
                            <p class="text-muted">Hãy tạo bài viết đầu tiên của bạn!</p>
                            <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Tạo bài viết
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pagination -->
@if($posts->hasPages())
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted">
        Hiển thị {{ $posts->firstItem() }} - {{ $posts->lastItem() }} của {{ $posts->total() }} bài viết
    </div>
    <div>
        {{ $posts->links() }}
    </div>
</div>
@endif
@endsection