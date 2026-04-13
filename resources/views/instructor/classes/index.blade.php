@extends('layouts.app')

@section('title', 'Lớp tôi đang dạy')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Lớp tôi đang dạy</h1>
            <p class="text-muted mb-0">Giảng viên có thể xem từng lớp, danh sách học viên và xuất file CSV mở bằng Excel.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('instructor.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Về dashboard
            </a>
            <a href="{{ route('instructor.courses.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-book-open me-2"></i>Khóa học của tôi
            </a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-sm-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Tổng lớp phụ trách</div>
                    <div class="display-6 fw-bold">{{ number_format($stats['total_classes']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Lớp đang mở</div>
                    <div class="display-6 fw-bold">{{ number_format($stats['active_classes']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Học viên đã duyệt</div>
                    <div class="display-6 fw-bold">{{ number_format($stats['students']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-sm-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <div class="text-muted small mb-2">Pending / Waitlist</div>
                    <div class="display-6 fw-bold">{{ number_format($stats['pending'] + $stats['waitlist']) }}</div>
                    <div class="small text-muted mt-2">{{ number_format($stats['pending']) }} chờ duyệt · {{ number_format($stats['waitlist']) }} waitlist</div>
                </div>
            </div>
        </div>
    </div>

    @if($classes->isNotEmpty())
        <div class="d-grid gap-4">
            @foreach($classes as $class)
                @php
                    $approvedStudents = $class->enrollments->whereIn('status', ['approved', 'completed'])->count();
                    $pendingStudents = $class->enrollments->where('status', 'pending')->count();
                @endphp
                <section class="card shadow-sm border-0">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                                <h2 class="h5 mb-0">{{ $class->name }}</h2>
                                <span class="badge bg-{{ $class->status_badge }}">{{ $class->status_text }}</span>
                                <span class="badge bg-light text-dark border">{{ $class->delivery_mode_label }}</span>
                            </div>
                            <div class="text-muted">{{ $class->course->title ?? 'Khóa học chưa đồng bộ' }}</div>
                            <div class="small text-muted mt-2 d-flex flex-wrap gap-3">
                                <span><i class="fas fa-calendar-day me-1"></i>{{ optional($class->start_date)->format('d/m/Y') ?: 'Chưa có ngày khai giảng' }}</span>
                                <span><i class="fas fa-clock me-1"></i>{{ $class->schedule_text ?: 'Chưa cập nhật lịch học' }}</span>
                                <span><i class="fas fa-chair me-1"></i>{{ is_null($class->remaining_slots) ? 'Không giới hạn' : ('Còn ' . $class->remaining_slots . ' chỗ') }}</span>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('instructor.classes.export-students', $class) }}" class="btn btn-success btn-sm">
                                <i class="fas fa-file-excel me-2"></i>Xuất danh sách học viên
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small">Học viên đã duyệt</div>
                                    <div class="fs-4 fw-bold">{{ number_format($approvedStudents) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small">Hồ sơ pending</div>
                                    <div class="fs-4 fw-bold">{{ number_format($pendingStudents) }}</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="border rounded p-3 h-100">
                                    <div class="text-muted small">Hàng chờ / giữ chỗ</div>
                                    <div class="fs-4 fw-bold">{{ number_format($class->waitlist_count + $class->held_seats_count) }}</div>
                                </div>
                            </div>
                        </div>

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
                                            <td colspan="5" class="text-center py-4 text-muted">Lớp này chưa có học viên nào.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    @else
        <div class="card shadow-sm border-0">
            <div class="card-body text-center py-5">
                <i class="fas fa-users-class fa-3x text-muted mb-3"></i>
                <h2 class="h4">Bạn chưa được phân công lớp nào</h2>
                <p class="text-muted mb-4">Khi admin gán lớp cho bạn, danh sách lớp và học viên sẽ hiện ở đây.</p>
                <a href="{{ route('instructor.courses.index') }}" class="btn btn-primary">
                    <i class="fas fa-book-open me-2"></i>Xem khóa học của tôi
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
