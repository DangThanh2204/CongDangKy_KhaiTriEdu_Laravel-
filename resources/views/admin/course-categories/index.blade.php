@extends('layouts.admin')

@section('title', 'Quản lý nhóm ngành')
@section('page-title', 'Quản lý nhóm ngành')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">Danh sách nhóm ngành</h4>
            <p class="text-muted mb-0">`course_categories` được dùng để phân nhóm ngành cho các khóa học.</p>
        </div>
        <a href="{{ route('admin.course-categories.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Thêm nhóm ngành</a>
    </div>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card users"><div class="stat-icon"><i class="fas fa-folder"></i></div><div class="stat-number">{{ $stats['totalCategories'] }}</div><div class="stat-label">Tổng nhóm ngành</div></div>
    <div class="stat-card courses"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div class="stat-number">{{ $stats['activeCategories'] }}</div><div class="stat-label">Đang hoạt động</div></div>
    <div class="stat-card revenue"><div class="stat-icon"><i class="fas fa-pause-circle"></i></div><div class="stat-number">{{ $stats['inactiveCategories'] }}</div><div class="stat-label">Đang ẩn</div></div>
    <div class="stat-card orders"><div class="stat-icon"><i class="fas fa-book"></i></div><div class="stat-number">{{ $stats['totalCourses'] }}</div><div class="stat-label">Tổng khóa học</div></div>
</div>

<div class="card mb-4"><div class="card-body"><form method="GET" class="row g-3 align-items-end"><div class="col-md-4"><label class="form-label">Tìm kiếm</label><input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Tên nhóm ngành..."></div><div class="col-md-3"><label class="form-label">Trạng thái</label><select name="status" class="form-select"><option value="">Tất cả</option><option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option><option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Đang ẩn</option></select></div><div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Lọc</button></div><div class="col-md-2"><a href="{{ route('admin.course-categories.index') }}" class="btn btn-outline-secondary w-100"><i class="fas fa-refresh me-2"></i>Reset</a></div></form></div></div>

<div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0 align-middle"><thead class="table-light"><tr><th class="ps-4">Nhóm ngành</th><th>Nhóm cha</th><th>Số khóa học</th><th>Thứ tự</th><th>Trạng thái</th><th>Ngày tạo</th><th class="text-end pe-4">Thao tác</th></tr></thead><tbody>@forelse($categories as $category)<tr><td class="ps-4"><div class="d-flex align-items-center gap-3">@if($category->icon)<i class="{{ $category->icon }}" style="color: {{ $category->color }}"></i>@endif<div><h6 class="mb-1">{{ $category->name }}</h6><small class="text-muted">/{{ $category->slug }}</small></div></div></td><td>{{ $category->parent->name ?? 'Nhóm gốc' }}</td><td><span class="badge bg-primary">{{ $category->courses_count }}</span></td><td>{{ $category->order }}</td><td>@if($category->is_active)<span class="badge bg-success">Đang hoạt động</span>@else<span class="badge bg-secondary">Đang ẩn</span>@endif</td><td>{{ $category->created_at->format('d/m/Y') }}</td><td class="text-end pe-4"><div class="btn-group btn-group-sm"><a href="{{ route('admin.course-categories.edit', $category) }}" class="btn btn-outline-warning" title="Chỉnh sửa"><i class="fas fa-edit"></i></a><form method="POST" action="{{ route('admin.course-categories.toggle-status', $category) }}" class="d-inline">@csrf @method('PATCH')<button type="submit" class="btn btn-outline-info" title="Bật/tắt hiển thị"><i class="fas fa-power-off"></i></button></form><form method="POST" action="{{ route('admin.course-categories.destroy', $category) }}" class="d-inline delete-category-form">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger" title="Xóa nhóm ngành"><i class="fas fa-trash"></i></button></form></div></td></tr>@empty<tr><td colspan="7" class="text-center py-5"><i class="fas fa-folder-open fa-3x text-muted mb-3"></i><h5 class="text-muted">Chưa có nhóm ngành nào</h5><p class="text-muted">Hãy tạo nhóm ngành đầu tiên để phân loại khóa học.</p><a href="{{ route('admin.course-categories.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Tạo nhóm ngành</a></td></tr>@endforelse</tbody></table></div></div></div>

@if($categories->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4"><div class="text-muted">Hiển thị {{ $categories->firstItem() }} - {{ $categories->lastItem() }} của {{ $categories->total() }} nhóm ngành</div><div>{{ $categories->links() }}</div></div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-category-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const name = this.closest('tr').querySelector('h6')?.textContent || 'nhóm ngành';
            if (!confirm(`Bạn có chắc muốn xóa nhóm ngành "${name}"?`)) event.preventDefault();
        });
    });
});
</script>
@endsection
