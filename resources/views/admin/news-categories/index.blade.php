@extends('layouts.admin')

@section('title', 'Quản lý Danh mục Tin tức')
@section('page-title', 'Quản lý Danh mục Tin tức')

@section('content')
<!-- Header với thống kê -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Danh sách danh mục</h4>
                <p class="text-muted mb-0">Quản lý tất cả danh mục tin tức trên hệ thống</p>
            </div>
            <a href="{{ route('admin.news-categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Thêm danh mục
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid mb-4">
    <div class="stat-card users">
        <div class="stat-icon">
            <i class="fas fa-folder"></i>
        </div>
        <div class="stat-number">{{ $stats['totalCategories'] }}</div>
        <div class="stat-label">Tổng danh mục</div>
    </div>
    
    <div class="stat-card courses">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-number">{{ $stats['activeCategories'] }}</div>
        <div class="stat-label">Đang hoạt động</div>
    </div>
    
    <div class="stat-card revenue">
        <div class="stat-icon">
            <i class="fas fa-pause-circle"></i>
        </div>
        <div class="stat-number">{{ $stats['inactiveCategories'] }}</div>
        <div class="stat-label">Ngừng hoạt động</div>
    </div>
</div>

<!-- Filters và Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm danh mục..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Ngừng hoạt động</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Lọc
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.news-categories.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-refresh me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Categories Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Tên danh mục</th>
                        <th>Màu sắc</th>
                        <th>Mô tả</th>
                        <th>Số bài viết</th>
                        <th>Thứ tự</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $category)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <span class="badge me-3" style="background: {{ $category->color }}; width: 16px; height: 16px; border-radius: 50%;"></span>
                                <div>
                                    <h6 class="mb-1">{{ $category->name }}</h6>
                                    <small class="text-muted">
                                        <code>{{ $category->color }}</code>
                                    </small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="color-preview rounded" style="background: {{ $category->color }}; width: 30px; height: 30px;"></div>
                        </td>
                        <td>
                            @if($category->description)
                                <span class="text-muted">{{ Str::limit($category->description, 50) }}</span>
                            @else
                                <span class="text-muted">Không có mô tả</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-info">{{ $category->posts_count ?? 0 }}</span>
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $category->order }}</span>
                        </td>
                        <td>
                            @if($category->is_active)
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>Hoạt động
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-pause me-1"></i>Ngừng hoạt động
                                </span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $category->created_at->format('d/m/Y') }}</small>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <!-- Edit Button -->
                                <a href="{{ route('admin.news-categories.edit', $category) }}" 
                                   class="btn btn-outline-warning"
                                   title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Toggle Status Button -->
                                <form method="POST" action="{{ route('admin.news-categories.toggle-status', $category) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-info" 
                                            title="{{ $category->is_active ? 'Ngừng hoạt động' : 'Kích hoạt' }}">
                                        <i class="fas fa-power-off"></i>
                                    </button>
                                </form>
                                
                                <!-- Delete Button -->
                                <form method="POST" action="{{ route('admin.news-categories.destroy', $category) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" 
                                            title="Xóa danh mục">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có danh mục nào</h5>
                            <p class="text-muted">Hãy tạo danh mục đầu tiên của bạn!</p>
                            <a href="{{ route('admin.news-categories.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Tạo danh mục
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
@if($categories->hasPages())
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted">
        Hiển thị {{ $categories->firstItem() }} - {{ $categories->lastItem() }} của {{ $categories->total() }} danh mục
    </div>
    <div>
        {{ $categories->links() }}
    </div>
</div>
@endif

<style>
.color-preview {
    border: 2px solid #e9ecef;
    cursor: pointer;
}
.btn-group .btn {
    border-radius: 6px;
    margin: 0 2px;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xác nhận xóa
    const deleteForms = document.querySelectorAll('form[method="DELETE"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const categoryName = this.closest('tr').querySelector('h6').textContent;
            if (!confirm(`Bạn có chắc chắn muốn xóa danh mục "${categoryName}"? Hành động này không thể hoàn tác!`)) {
                e.preventDefault();
            }
        });
    });

    // Xác nhận thay đổi trạng thái
    const toggleForms = document.querySelectorAll('form[action*="toggle-status"]');
    toggleForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const categoryName = this.closest('tr').querySelector('h6').textContent;
            const isActive = this.closest('tr').querySelector('.badge.bg-success') !== null;
            const action = isActive ? 'ngừng hoạt động' : 'kích hoạt';
            if (!confirm(`Bạn có chắc chắn muốn ${action} danh mục "${categoryName}"?`)) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endsection