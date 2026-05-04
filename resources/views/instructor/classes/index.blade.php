@extends('layouts.app')

@section('title', 'Lớp tôi đang dạy')

@push('styles')
<style>
    .class-toggle.collapsed .when-expanded { display: none; }
    .class-toggle:not(.collapsed) .when-collapsed { display: none; }
    .stat-icon { width: 40px; height: 40px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; }
    .class-card { transition: box-shadow .15s ease; }
    .class-card:hover { box-shadow: 0 .5rem 1rem rgba(0,0,0,.08) !important; }
    @media (max-width: 576px) {
        .class-card .card-header .btn-group-actions { width: 100%; }
        .class-card .card-header .btn-group-actions .btn { flex: 1; }
    }
</style>
@endpush

@section('content')
<div class="container py-4 py-md-5">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1"><i class="fas fa-chalkboard-teacher me-2 text-primary"></i>Lớp tôi đang dạy</h1>
            <p class="text-muted mb-0">Xem từng lớp, danh sách học viên và xuất file CSV mở bằng Excel.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('instructor.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Dashboard
            </a>
            <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-book-open me-2"></i>Khóa học của tôi
            </a>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-primary bg-opacity-10 text-primary"><i class="fas fa-layer-group"></i></span>
                    <div>
                        <div class="text-muted small">Tổng lớp phụ trách</div>
                        <div class="fs-3 fw-bold lh-1">{{ number_format($stats['total_classes']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-success bg-opacity-10 text-success"><i class="fas fa-circle-play"></i></span>
                    <div>
                        <div class="text-muted small">Lớp đang mở</div>
                        <div class="fs-3 fw-bold lh-1">{{ number_format($stats['active_classes']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-info bg-opacity-10 text-info"><i class="fas fa-user-check"></i></span>
                    <div>
                        <div class="text-muted small">Học viên đã duyệt</div>
                        <div class="fs-3 fw-bold lh-1">{{ number_format($stats['students']) }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <span class="stat-icon bg-warning bg-opacity-10 text-warning"><i class="fas fa-hourglass-half"></i></span>
                    <div>
                        <div class="text-muted small">Pending / Waitlist</div>
                        <div class="fs-3 fw-bold lh-1">{{ number_format($stats['pending'] + $stats['waitlist']) }}</div>
                        <div class="small text-muted">{{ number_format($stats['pending']) }} chờ duyệt · {{ number_format($stats['waitlist']) }} waitlist</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($classes->isNotEmpty())
        {{-- Filter bar --}}
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body py-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                            <input type="search" id="classSearch" class="form-control" placeholder="Tìm lớp theo tên hoặc khóa học...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="classStatusFilter" class="form-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="active">Đang mở</option>
                            <option value="inactive">Tạm dừng</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-md-end small text-muted">
                        <span id="classFilterCount">{{ $classes->count() }}/{{ $classes->count() }} lớp</span>
                    </div>
                </div>
            </div>
        </div>

        <div id="classesList" class="d-grid gap-4">
            @foreach($classes as $class)
                @php
                    $approvedStudents = $class->enrollments->whereIn('status', ['approved', 'completed'])->count();
                    $pendingStudents = $class->enrollments->where('status', 'pending')->count();
                    $totalEnrollments = $class->enrollments->count();
                    $collapseId = 'classCollapse_' . $class->id;
                @endphp
                <section class="card class-card shadow-sm border-0"
                         data-class-card
                         data-class-name="{{ \Illuminate\Support\Str::lower($class->name) }}"
                         data-course-name="{{ \Illuminate\Support\Str::lower($class->course->title ?? '') }}"
                         data-class-status="{{ $class->status }}">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div class="flex-grow-1" style="min-width: 0;">
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <h2 class="h5 mb-0">{{ $class->name }}</h2>
                                <span class="badge bg-{{ $class->status_badge }}">{{ $class->status_text }}</span>
                                <span class="badge bg-light text-dark border">{{ $class->delivery_mode_label }}</span>
                            </div>
                            <div class="text-muted">
                                <i class="fas fa-book me-1"></i>{{ $class->course->title ?? 'Khóa học chưa đồng bộ' }}
                            </div>
                            <div class="small text-muted mt-2 d-flex flex-wrap gap-3">
                                <span><i class="fas fa-calendar-day me-1"></i>{{ optional($class->start_date)->format('d/m/Y') ?: 'Chưa có ngày khai giảng' }}</span>
                                <span><i class="fas fa-clock me-1"></i>{{ $class->schedule_text ?: 'Chưa cập nhật lịch học' }}</span>
                                <span><i class="fas fa-chair me-1"></i>{{ is_null($class->remaining_slots) ? 'Không giới hạn' : ('Còn ' . $class->remaining_slots . ' chỗ') }}</span>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2 btn-group-actions">
                            @if($class->course)
                                <a href="{{ route('instructor.courses.enrollments.index', $class->course) }}" class="btn btn-outline-primary btn-sm" title="Quản lý đăng ký khóa">
                                    <i class="fas fa-user-cog me-2"></i>Quản lý đăng ký
                                </a>
                            @endif
                            <a href="{{ route('instructor.classes.export-students', $class) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel me-2"></i>Xuất CSV
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 text-center">
                                    <i class="fas fa-user-check text-success mb-1"></i>
                                    <div class="text-muted small">Học viên đã duyệt</div>
                                    <div class="fs-4 fw-bold">{{ number_format($approvedStudents) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 text-center">
                                    <i class="fas fa-hourglass-half text-warning mb-1"></i>
                                    <div class="text-muted small">Hồ sơ pending</div>
                                    <div class="fs-4 fw-bold">{{ number_format($pendingStudents) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100 text-center">
                                    <i class="fas fa-list-ol text-info mb-1"></i>
                                    <div class="text-muted small">Hàng chờ / giữ chỗ</div>
                                    <div class="fs-4 fw-bold">{{ number_format($class->waitlist_count + $class->held_seats_count) }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h3 class="h6 mb-0"><i class="fas fa-users me-2 text-secondary"></i>Danh sách học viên <span class="text-muted">({{ $totalEnrollments }})</span></h3>
                            @if($totalEnrollments > 0)
                                <button type="button" class="btn btn-sm btn-outline-secondary class-toggle" data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}" aria-expanded="true" aria-controls="{{ $collapseId }}">
                                    <span class="when-expanded"><i class="fas fa-chevron-up me-1"></i>Thu gọn</span>
                                    <span class="when-collapsed"><i class="fas fa-chevron-down me-1"></i>Mở rộng</span>
                                </button>
                            @endif
                        </div>

                        <div id="{{ $collapseId }}" class="collapse show">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Học viên</th>
                                            <th>Tài khoản</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày đăng ký</th>
                                            <th>Ghi chú</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($class->enrollments as $enrollment)
                                            @php
                                                $student = $enrollment->user;
                                                $badgeClass = $enrollment->status_color === 'warning' ? 'text-dark' : '';
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="fw-semibold">{{ $student?->fullname ?: $student?->username ?: 'Chưa có tên' }}</div>
                                                    <div class="small text-muted">{{ $student?->email ?? 'Chưa có email' }}</div>
                                                </td>
                                                <td>{{ $student?->username ?? '--' }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $enrollment->status_color }} {{ $badgeClass }}">{{ $enrollment->status_text }}</span>
                                                </td>
                                                <td>{{ optional($enrollment->created_at)->format('d/m/Y H:i') ?: '--' }}</td>
                                                <td class="text-muted small">{{ $enrollment->notes ?: '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">
                                                    <i class="fas fa-inbox fa-2x d-block mb-2 opacity-50"></i>
                                                    Lớp này chưa có học viên nào.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            @endforeach
        </div>

        <div id="emptyFilterState" class="text-center text-muted py-5 d-none">
            <i class="fas fa-search fa-2x mb-3 opacity-50"></i>
            <p class="mb-0">Không có lớp nào khớp bộ lọc.</p>
        </div>
    @else
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="fas fa-chalkboard fa-3x text-muted mb-3 opacity-50"></i>
                <h2 class="h4">Bạn chưa được phân công lớp nào</h2>
                <p class="text-muted mb-4">Khi admin gán lớp cho bạn, danh sách lớp và học viên sẽ hiện ở đây.</p>
                <a href="{{ route('instructor.courses.index') }}" class="btn btn-primary">
                    <i class="fas fa-book-open me-2"></i>Xem khóa học của tôi
                </a>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
(function () {
    const search = document.getElementById('classSearch');
    const statusFilter = document.getElementById('classStatusFilter');
    const list = document.getElementById('classesList');
    const counter = document.getElementById('classFilterCount');
    const emptyState = document.getElementById('emptyFilterState');
    if (!list) return;

    const cards = Array.from(list.querySelectorAll('[data-class-card]'));
    const total = cards.length;

    function apply() {
        const q = (search?.value || '').toLowerCase().trim();
        const status = statusFilter?.value || '';
        let visible = 0;
        cards.forEach(card => {
            const name = card.dataset.className || '';
            const course = card.dataset.courseName || '';
            const cardStatus = card.dataset.classStatus || '';
            const matchQ = !q || name.includes(q) || course.includes(q);
            const matchS = !status || cardStatus === status;
            const show = matchQ && matchS;
            card.classList.toggle('d-none', !show);
            if (show) visible++;
        });
        if (counter) counter.textContent = `${visible}/${total} lớp`;
        if (emptyState) emptyState.classList.toggle('d-none', visible !== 0);
    }

    search?.addEventListener('input', apply);
    statusFilter?.addEventListener('change', apply);
})();
</script>
@endpush
@endsection
