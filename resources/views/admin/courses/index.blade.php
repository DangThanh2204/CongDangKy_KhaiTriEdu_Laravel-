@extends('layouts.admin')

@section('title', 'Quản lý khóa học')
@section('page-title', 'Quản lý khóa học')

@section('content')
<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">Danh sách khóa học</h4>
            <p class="text-muted mb-0">Quản lý khóa học theo nhóm ngành, module và đợt học.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.courses.export', request()->query(), false) }}" class="btn btn-outline-success"><i class="fas fa-file-excel me-2"></i>Xuất Excel</a>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Thêm khóa học</a>
        </div>
    </div>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card users"><div class="stat-icon"><i class="fas fa-book"></i></div><div class="stat-number">{{ $stats['totalCourses'] }}</div><div class="stat-label">Tổng khóa học</div></div>
    <div class="stat-card courses"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div class="stat-number">{{ $stats['publishedCourses'] }}</div><div class="stat-label">Đã xuất bản</div></div>
    <div class="stat-card revenue"><div class="stat-icon"><i class="fas fa-edit"></i></div><div class="stat-number">{{ $stats['draftCourses'] }}</div><div class="stat-label">Bản nháp</div></div>
    <div class="stat-card orders"><div class="stat-icon"><i class="fas fa-star"></i></div><div class="stat-number">{{ $stats['featuredCourses'] }}</div><div class="stat-label">Nổi bật</div></div>
</div>

<div class="card mb-4"><div class="card-body"><form method="GET" class="row g-3 align-items-end"><div class="col-md-3"><label class="form-label">Tìm kiếm</label><input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Tên khóa học..."></div><div class="col-md-2"><label class="form-label">Trạng thái</label><select name="status" class="form-select"><option value="">Tất cả</option><option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Xuất bản</option><option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Bản nháp</option></select></div><div class="col-md-2"><label class="form-label">Nhóm ngành</label><select name="category" class="form-select"><option value="">Tất cả</option>@foreach($categories as $category)<option value="{{ $category->id }}" {{ (string) request('category') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select></div><div class="col-md-2"><label class="form-label">Từ ngày</label><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div><div class="col-md-2"><label class="form-label">Đến ngày</label><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div><div class="col-md-1 d-grid"><button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button></div><div class="col-12"><a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary"><i class="fas fa-refresh me-2"></i>Reset bộ lọc</a></div></form></div></div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Khóa học</th>
                        <th>Nhóm ngành</th>
                        <th>Module</th>
                        <th>Đợt học</th>
                        <th>Giá</th>
                        <th>Học viên</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                        <tr>
                            <td class="ps-4">
                                <div>
                                    <h6 class="mb-1">{{ $course->title }}</h6>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <span class="badge bg-light text-dark border">{{ ucfirst($course->learning_type ?? 'online') }}</span>
                                        <span class="badge bg-info text-dark">{{ $course->level }}</span>
                                        @if($course->is_featured)<span class="badge bg-warning text-dark">Nổi bật</span>@endif
                                        @if($course->is_popular)<span class="badge bg-danger">Phổ biến</span>@endif
                                    </div>
                                </div>
                            </td>
                            <td>{{ $course->category->name ?? 'Chưa phân loại' }}</td>
                            <td><span class="badge bg-primary">{{ $course->modules_count }}</span></td>
                            <td><span class="badge bg-secondary">{{ $course->classes_count }}</span></td>
                            <td>@if($course->sale_price)<div><span class="text-danger fw-bold">{{ number_format($course->sale_price) }}₫</span><small class="text-muted text-decoration-line-through d-block">{{ number_format($course->price) }}₫</small></div>@else<span class="fw-bold">{{ number_format($course->price) }}₫</span>@endif</td>
                            <td>{{ $course->students_count }}</td>
                            <td>@if($course->status === 'published')<span class="badge bg-success">Xuất bản</span>@else<span class="badge bg-secondary">Bản nháp</span>@endif</td>
                            <td>{{ $course->created_at->format('d/m/Y') }}</td>
                            <td class="text-end pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-outline-warning" title="Chỉnh sửa"><i class="fas fa-edit"></i></a>
                                    <a href="{{ route('admin.classes.index', ['course_id' => $course->id]) }}" class="btn btn-outline-dark" title="Quản lý đợt học"><i class="fas fa-calendar-alt"></i></a>
                                    <form method="POST" action="{{ route('admin.courses.toggle-featured', $course) }}" class="d-inline">@csrf @method('PATCH')<button type="submit" class="btn btn-outline-info" title="Đổi trạng thái nổi bật"><i class="fas fa-star"></i></button></form>
                                    <form method="POST" action="{{ route('admin.courses.toggle-popular', $course) }}" class="d-inline">@csrf @method('PATCH')<button type="submit" class="btn btn-outline-primary" title="Đổi trạng thái phổ biến"><i class="fas fa-fire"></i></button></form>
                                    <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" class="d-inline delete-course-form">@csrf @method('DELETE')<button type="submit" class="btn btn-outline-danger" title="Xóa khóa học"><i class="fas fa-trash"></i></button></form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="text-center py-5"><i class="fas fa-book fa-3x text-muted mb-3"></i><h5 class="text-muted">Chưa có khóa học nào</h5><p class="text-muted">Hãy tạo khóa học đầu tiên để bắt đầu quản lý nội dung và đợt học.</p><a href="{{ route('admin.courses.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Tạo khóa học</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($courses->hasPages())
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">Hiển thị {{ $courses->firstItem() }} - {{ $courses->lastItem() }} của {{ $courses->total() }} khóa học</div>
        <div>{{ $courses->links() }}</div>
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-course-form').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            const courseTitle = this.closest('tr').querySelector('h6')?.textContent || 'khóa học';
            if (!confirm(`Bạn có chắc muốn xóa khóa học "${courseTitle}"? Hành động này không thể hoàn tác.`)) {
                event.preventDefault();
            }
        });
    });
});
</script>
@endsection
