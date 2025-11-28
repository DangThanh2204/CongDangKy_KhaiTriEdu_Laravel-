@extends('layouts.admin')

@section('title', 'Quản lý Khóa học')
@section('page-title', 'Quản lý Khóa học')

@section('content')
<!-- Header với thống kê -->
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Danh sách khóa học</h4>
                <p class="text-muted mb-0">Quản lý tất cả khóa học trên hệ thống</p>
            </div>
            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Thêm khóa học
            </a>
        </div>
    </div>
</div>

<!-- Stats Cards -->
<div class="stats-grid mb-4">
    <div class="stat-card users">
        <div class="stat-icon">
            <i class="fas fa-book"></i>
        </div>
        <div class="stat-number">{{ $stats['totalCourses'] }}</div>
        <div class="stat-label">Tổng khóa học</div>
    </div>
    
    <div class="stat-card courses">
        <div class="stat-icon">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-number">{{ $stats['publishedCourses'] }}</div>
        <div class="stat-label">Đã xuất bản</div>
    </div>
    
    <div class="stat-card revenue">
        <div class="stat-icon">
            <i class="fas fa-edit"></i>
        </div>
        <div class="stat-number">{{ $stats['draftCourses'] }}</div>
        <div class="stat-label">Bản nháp</div>
    </div>
    
    <div class="stat-card orders">
        <div class="stat-icon">
            <i class="fas fa-star"></i>
        </div>
        <div class="stat-number">{{ $stats['featuredCourses'] }}</div>
        <div class="stat-label">Nổi bật</div>
    </div>
</div>

<!-- Filters và Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Tìm kiếm</label>
                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm khóa học..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Trạng thái</label>
                <select name="status" class="form-select">
                    <option value="">Tất cả trạng thái</option>
                    <option value="published" {{ request('status') == 'published' ? 'selected' : '' }}>Đã xuất bản</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Bản nháp</option>
                </select>
            </div>
            <div class="col-md-2">
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
            <div class="col-md-2">
                <label class="form-label">Giảng viên</label>
                <select name="instructor" class="form-select">
                    <option value="">Tất cả giảng viên</option>
                    @foreach($instructors as $instructor)
                    <option value="{{ $instructor->id }}" {{ request('instructor') == $instructor->id ? 'selected' : '' }}>
                        {{ $instructor->fullname }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Lọc
                </button>
            </div>
            <div class="col-md-1">
                <a href="{{ route('admin.courses.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-refresh me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Courses Table -->
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">Khóa học</th>
                        <th>Danh mục</th>
                        <th>Giảng viên</th>
                        <th>Giá</th>
                        <th>Học viên</th>
                        <th>Đánh giá</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($courses as $course)
                    <tr>
                        <td class="ps-4">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-book text-muted fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1">{{ $course->title }}</h6>
                                    <div class="d-flex align-items-center">
                                        @if($course->is_featured)
                                        <span class="badge bg-warning me-2">
                                            <i class="fas fa-star me-1"></i>Nổi bật
                                        </span>
                                        @endif
                                        @if($course->is_popular)
                                        <span class="badge bg-info">
                                            <i class="fas fa-fire me-1"></i>Phổ biến
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($course->category)
                            <span class="badge" style="background: {{ $course->category->color }}; color: white;">
                                {{ $course->category->name }}
                            </span>
                            @else
                            <span class="badge bg-secondary">Chưa phân loại</span>
                            @endif
                        </td>
                        <td>
                            <span class="fw-medium">{{ $course->instructor->fullname }}</span>
                        </td>
                        <td>
                            @if($course->sale_price)
                            <div>
                                <span class="text-danger fw-bold">{{ number_format($course->sale_price) }}₫</span>
                                <small class="text-muted text-decoration-line-through">{{ number_format($course->price) }}₫</small>
                            </div>
                            @else
                            <span class="fw-bold">{{ number_format($course->price) }}₫</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-primary">{{ $course->students_count }}</span>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-star text-warning me-1"></i>
                                <span class="fw-medium">{{ $course->rating }}</span>
                                <small class="text-muted ms-1">({{ $course->total_rating }})</small>
                            </div>
                        </td>
                        <td>
                            @if($course->status === 'published')
                                <span class="badge bg-success">
                                    <i class="fas fa-check me-1"></i>Đã xuất bản
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="fas fa-edit me-1"></i>Bản nháp
                                </span>
                            @endif
                        </td>
                        <td>
                            <small class="text-muted">{{ $course->created_at->format('d/m/Y') }}</small>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group btn-group-sm">
                                <!-- Edit Button -->
                                <a href="{{ route('admin.courses.edit', $course) }}" 
                                   class="btn btn-outline-warning"
                                   title="Chỉnh sửa">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <!-- Toggle Featured Button -->
                                <form method="POST" action="{{ route('admin.courses.toggle-featured', $course) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-info" 
                                            title="{{ $course->is_featured ? 'Bỏ nổi bật' : 'Đánh dấu nổi bật' }}">
                                        <i class="fas fa-star"></i>
                                    </button>
                                </form>

                                <!-- Toggle Popular Button -->
                                <form method="POST" action="{{ route('admin.courses.toggle-popular', $course) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-outline-primary" 
                                            title="{{ $course->is_popular ? 'Bỏ phổ biến' : 'Đánh dấu phổ biến' }}">
                                        <i class="fas fa-fire"></i>
                                    </button>
                                </form>
                                
                                <!-- Delete Button -->
                                <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" 
                                      class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger" 
                                            title="Xóa khóa học">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5">
                            <i class="fas fa-book fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Chưa có khóa học nào</h5>
                            <p class="text-muted">Hãy tạo khóa học đầu tiên của bạn!</p>
                            <a href="{{ route('admin.courses.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Tạo khóa học
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
@if($courses->hasPages())
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted">
        Hiển thị {{ $courses->firstItem() }} - {{ $courses->lastItem() }} của {{ $courses->total() }} khóa học
    </div>
    <div>
        {{ $courses->links() }}
    </div>
</div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Xác nhận xóa
    const deleteForms = document.querySelectorAll('form[method="DELETE"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const courseTitle = this.closest('tr').querySelector('h6').textContent;
            if (!confirm(`Bạn có chắc chắn muốn xóa khóa học "${courseTitle}"? Hành động này không thể hoàn tác!`)) {
                e.preventDefault();
            }
        });
    });

    // Xác nhận thay đổi trạng thái nổi bật
    const toggleFeaturedForms = document.querySelectorAll('form[action*="toggle-featured"]');
    toggleFeaturedForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const courseTitle = this.closest('tr').querySelector('h6').textContent;
            const isFeatured = this.closest('tr').querySelector('.badge.bg-warning') !== null;
            const action = isFeatured ? 'bỏ đánh dấu nổi bật' : 'đánh dấu nổi bật';
            if (!confirm(`Bạn có chắc chắn muốn ${action} khóa học "${courseTitle}"?`)) {
                e.preventDefault();
            }
        });
    });

    // Xác nhận thay đổi trạng thái phổ biến
    const togglePopularForms = document.querySelectorAll('form[action*="toggle-popular"]');
    togglePopularForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const courseTitle = this.closest('tr').querySelector('h6').textContent;
            const isPopular = this.closest('tr').querySelector('.badge.bg-info') !== null;
            const action = isPopular ? 'bỏ đánh dấu phổ biến' : 'đánh dấu phổ biến';
            if (!confirm(`Bạn có chắc chắn muốn ${action} khóa học "${courseTitle}"?`)) {
                e.preventDefault();
            }
        });
    });
});
</script>
@endsection