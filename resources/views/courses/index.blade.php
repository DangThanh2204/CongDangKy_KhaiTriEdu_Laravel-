@extends('layouts.app')

@section('title', 'Danh sách khóa học')

@section('content')
@php
    $pendingCourses = $pendingCourses ?? [];
@endphp
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="fw-bold mb-1">Danh sách khóa học</h1>
            <p class="text-muted mb-0">{{ request('q') ? 'Kết quả tra cứu cho "' . request('q') . '". Bạn có thể tiếp tục lọc theo nhóm ngành, cấp độ hoặc hình thức học.' : 'Khám phá khóa học theo nhóm ngành, hình thức đào tạo và lộ trình module.' }}</p>
        </div>
        <div class="dropdown">
            @php
                $filterLabel = 'Lọc khóa học';
                if (request()->category) {
                    $cat = $categories->firstWhere('id', request()->category);
                    if ($cat) {
                        $filterLabel = 'Nhóm ngành: ' . $cat->name;
                    }
                } elseif (request()->delivery_mode === 'online') {
                    $filterLabel = 'Hình thức: Online';
                } elseif (request()->delivery_mode === 'offline') {
                    $filterLabel = 'Hình thức: Offline';
                } elseif (request()->filter) {
                    $filterLabel = ucfirst(request()->filter);
                } elseif (request()->level) {
                    $filterLabel = 'Mức: ' . ucfirst(request()->level);
                } elseif (request()->q) {
                    $filterLabel = 'Từ khóa: ' . request()->q;
                }
            @endphp
            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown"><i class="fas fa-filter me-2"></i>{{ $filterLabel }}</button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item {{ !request()->filter && !request()->level && !request()->category && !request()->delivery_mode && !request()->q ? 'active' : '' }}" href="{{ route('courses.index', request()->except('filter', 'level', 'category', 'delivery_mode', 'q')) }}">Tất cả</a></li>
                <li><a class="dropdown-item {{ request()->filter === 'featured' ? 'active' : '' }}" href="{{ route('courses.index', array_merge(request()->query(), ['filter' => 'featured'])) }}">Nổi bật</a></li>
                <li><a class="dropdown-item {{ request()->filter === 'popular' ? 'active' : '' }}" href="{{ route('courses.index', array_merge(request()->query(), ['filter' => 'popular'])) }}">Phổ biến</a></li>
                <li><a class="dropdown-item {{ request()->delivery_mode === 'online' ? 'active' : '' }}" href="{{ route('courses.index', array_merge(request()->query(), ['delivery_mode' => 'online'])) }}">Online</a></li>
                <li><a class="dropdown-item {{ request()->delivery_mode === 'offline' ? 'active' : '' }}" href="{{ route('courses.index', array_merge(request()->query(), ['delivery_mode' => 'offline'])) }}">Offline</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item {{ request()->level === 'beginner' ? 'active' : '' }}" href="{{ route('courses.index', array_merge(request()->query(), ['level' => 'beginner'])) }}">Cơ bản</a></li>
                <li><a class="dropdown-item {{ request()->level === 'intermediate' ? 'active' : '' }}" href="{{ route('courses.index', array_merge(request()->query(), ['level' => 'intermediate'])) }}">Trung cấp</a></li>
                <li><a class="dropdown-item {{ request()->level === 'advanced' ? 'active' : '' }}" href="{{ route('courses.index', array_merge(request()->query(), ['level' => 'advanced'])) }}">Nâng cao</a></li>
                <li><hr class="dropdown-divider"></li>
                <li class="dropdown-header">Nhóm ngành</li>
                @foreach($categories as $cat)
                    <li><a class="dropdown-item {{ (string) request()->category === (string) $cat->id ? 'active' : '' }}" href="{{ route('courses.index', array_merge(request()->query(), ['category' => $cat->id])) }}">{{ $cat->name }}</a></li>
                @endforeach
            </ul>
        </div>
    </div>

    @if($courses->count() > 0)
        <div class="row g-4">
            @foreach($courses as $course)
                @php
                    $isEnrolled = in_array($course->id, $enrolledCourses, true);
                    $isPending = in_array($course->id, $pendingCourses, true);
                    $isOffline = $course->delivery_mode === 'offline';
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card course-card h-100 shadow-sm border-0">
                        <div class="position-relative">
                            <img src="{{ $course->thumbnail_url }}" class="card-img-top" alt="{{ $course->title }}" style="height: 220px; object-fit: cover;">
                            @if($course->is_featured)<div class="course-badge bg-warning text-dark">Nổi bật</div>@endif
                            @if($course->is_popular)<div class="course-badge bg-danger" style="top: 52px;">Phổ biến</div>@endif
                            @if($course->discount_percentage > 0)<div class="course-badge bg-success" style="top: 89px;">-{{ $course->discount_percentage }}%</div>@endif
                        </div>
                        <div class="card-body d-flex flex-column">
                            <div class="mb-3 d-flex flex-wrap gap-2">
                                <span class="badge bg-secondary">{{ $course->category->name ?? 'Chưa phân loại' }}</span>
                                <span class="badge bg-{{ $isOffline ? 'dark' : 'info text-dark' }}">{{ $course->delivery_mode_label }}</span>
                                <span class="badge bg-light text-dark border">{{ $course->modules_count }} module</span>
                            </div>
                            <h5 class="card-title fw-bold">{{ $course->title }}</h5>
                            <p class="text-muted flex-grow-1">{{ $course->short_description ?? \Illuminate\Support\Str::limit($course->description, 100) }}</p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    @if($course->sale_price)
                                        <span class="text-primary fw-bold h5 mb-0">{{ number_format($course->sale_price) }}₫</span>
                                        <small class="text-muted text-decoration-line-through ms-2">{{ number_format($course->price) }}₫</small>
                                    @else
                                        <span class="text-primary fw-bold h5 mb-0">{{ number_format($course->price) }}₫</span>
                                    @endif
                                </div>
                                <small class="text-muted"><i class="fas fa-clock me-1"></i>{{ $course->duration_label }}</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-3 small text-muted">
                                <span><i class="fas fa-users me-1"></i>{{ $course->students_count }} học viên</span>
                                <span><i class="fas fa-star text-warning me-1"></i>{{ $course->rating }} ({{ $course->total_rating }})</span>
                            </div>
                            <div class="mt-auto">
                                @if($isEnrolled)
                                    <button class="btn btn-success w-100" disabled><i class="fas fa-check me-2"></i>Đã đăng ký</button>
                                @elseif($isPending)
                                    <button class="btn btn-warning w-100" disabled><i class="fas fa-clock me-2"></i>Chờ admin duyệt</button>
                                @elseif($isOffline)
                                    <a href="{{ route('courses.show', $course) }}#intakes" class="btn btn-primary w-100"><i class="fas fa-paper-plane me-2"></i>Gửi yêu cầu đăng ký</a>
                                @else
                                    <form action="{{ route('courses.enroll', $course) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-plus me-2"></i>Đăng ký học ngay</button>
                                    </form>
                                @endif
                                <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-primary w-100 mt-2"><i class="fas fa-info-circle me-2"></i>Chi tiết</a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="row mt-5"><div class="col-12">{{ $courses->withQueryString()->links() }}</div></div>
    @else
        <div class="text-center py-5"><i class="fas fa-book fa-4x text-muted mb-3"></i><h3 class="text-muted">Chưa có khóa học nào</h3><p class="text-muted">Hiện tại chưa có khóa học phù hợp với bộ lọc này.</p></div>
    @endif
</div>

<style>
.course-badge { position: absolute; top: 15px; right: 15px; color: white; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; }
</style>
@endsection