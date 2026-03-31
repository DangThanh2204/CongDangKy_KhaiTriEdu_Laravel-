@extends('layouts.admin')

@section('title', 'Quản lý Đánh giá')
@section('page-title', 'Quản lý Đánh giá')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Danh sách đánh giá</h4>
                <p class="text-muted mb-0">Quản lý tất cả đánh giá khóa học và giảng viên.</p>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Khóa học</label>
                <select name="course" class="form-select">
                    <option value="">Tất cả khóa học</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ request('course') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Giảng viên</label>
                <select name="instructor" class="form-select">
                    <option value="">Tất cả giảng viên</option>
                    @foreach($instructors as $instructor)
                        <option value="{{ $instructor->id }}" {{ request('instructor') == $instructor->id ? 'selected' : '' }}>{{ $instructor->fullname }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Rating khóa học</label>
                <select name="rating" class="form-select">
                    <option value="">Tất cả</option>
                    @for($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" {{ request('rating') == $i ? 'selected' : '' }}>{{ $i }} sao</option>
                    @endfor
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-2"></i>Lọc
                </button>
            </div>
            <div class="col-md-2">
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-refresh me-2"></i>Reset
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Học viên</th>
                        <th>Khóa học</th>
                        <th>Giảng viên</th>
                        <th>Rating</th>
                        <th>Đánh giá giảng viên</th>
                        <th>Nội dung</th>
                        <th>Ngày</th>
                        <th class="text-end pe-4">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $review)
                        <tr>
                            <td>{{ $review->user->fullname ?? $review->user->username }}</td>
                            <td>{{ $review->course->title }}</td>
                            <td>{{ optional($review->instructor)->fullname ?? optional($review->instructor)->username ?? '-' }}</td>
                            <td>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star text-{{ $i <= $review->rating ? 'warning' : 'muted' }}"></i>
                                @endfor
                            </td>
                            <td>
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="fas fa-star text-{{ $i <= $review->instructor_rating ? 'warning' : 'muted' }}"></i>
                                @endfor
                            </td>
                            <td class="text-truncate" style="max-width: 200px;">{{ $review->comment ?? '-' }}</td>
                            <td>{{ $review->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end pe-4">
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn xóa đánh giá này?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Xóa
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">Chưa có đánh giá nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    {{ $reviews->withQueryString()->links() }}
</div>
@endsection
