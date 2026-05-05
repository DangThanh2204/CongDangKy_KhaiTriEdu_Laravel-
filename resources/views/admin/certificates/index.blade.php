@extends('layouts.admin')

@section('title', 'Quản lý chứng chỉ')
@section('page-title', 'Quản lý chứng chỉ')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-0">Chứng chỉ học viên hoàn thành</h4>
                <p class="text-muted mb-0">Xem học viên đã hoàn thành khóa học và quản lý chứng chỉ đã cấp.</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.certificates.export', request()->query(), false) }}" class="btn btn-outline-success">
                    <i class="fas fa-file-excel me-2"></i>Xuất Excel
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Học viên đã hoàn thành</div>
                <div class="fs-3 fw-bold text-primary">{{ number_format($stats['completed']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Đã cấp chứng chỉ</div>
                <div class="fs-3 fw-bold text-success">{{ number_format($stats['with_certificate']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small mb-1">Chưa có chứng chỉ</div>
                <div class="fs-3 fw-bold text-warning">{{ number_format($stats['without_certificate']) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Khóa học</label>
                <select name="course_id" class="form-select">
                    <option value="">Tất cả khóa học</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ (string) request('course_id') === (string) $course->id ? 'selected' : '' }}>
                            {{ $course->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tình trạng chứng chỉ</label>
                <select name="certificate_status" class="form-select">
                    <option value="">Tất cả</option>
                    <option value="with" {{ request('certificate_status') === 'with' ? 'selected' : '' }}>Đã cấp</option>
                    <option value="without" {{ request('certificate_status') === 'without' ? 'selected' : '' }}>Chưa cấp</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Tìm học viên</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Tên / email / username">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    <i class="fas fa-filter me-1"></i>Lọc
                </button>
                <a href="{{ route('admin.certificates.index') }}" class="btn btn-outline-secondary" title="Xóa bộ lọc">
                    <i class="fas fa-times"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Học viên</th>
                        <th>Khóa học / Lớp</th>
                        <th>Hoàn thành</th>
                        <th>Chứng chỉ</th>
                        <th class="text-end">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($enrollments as $enrollment)
                        @php $cert = $enrollment->certificate; @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $enrollment->user->fullname ?? $enrollment->user->username ?? '—' }}</div>
                                <div class="small text-muted">{{ $enrollment->user->email ?? '' }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $enrollment->course->title ?? '—' }}</div>
                                <div class="small text-muted">
                                    @if($enrollment->course)
                                        <span class="badge bg-light text-dark border">{{ $enrollment->course->delivery_mode_label ?? $enrollment->course->delivery_mode }}</span>
                                    @endif
                                    @if($enrollment->courseClass)
                                        · {{ $enrollment->courseClass->name }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($enrollment->completed_at)
                                    <div>{{ $enrollment->completed_at->format('d/m/Y') }}</div>
                                    <div class="small text-muted">{{ $enrollment->completed_at->format('H:i') }}</div>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($cert)
                                    <div class="fw-semibold text-success"><i class="fas fa-certificate me-1"></i>{{ $cert->certificate_no }}</div>
                                    <div class="small text-muted">Cấp {{ optional($cert->issued_at)->format('d/m/Y H:i') }}</div>
                                @else
                                    <span class="badge bg-warning text-dark">Chưa cấp</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-2">
                                    @if($cert)
                                        <a href="{{ route('certificates.verify', ['code' => $cert->certificate_no]) }}"
                                           target="_blank"
                                           class="btn btn-sm btn-outline-primary"
                                           title="Xem chứng chỉ">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <form action="{{ route('admin.certificates.revoke', $cert) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Thu hồi chứng chỉ {{ $cert->certificate_no }}?');"
                                                    title="Thu hồi">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.certificates.issue', $enrollment) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">
                                                <i class="fas fa-certificate me-1"></i>Cấp chứng chỉ
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                Chưa có học viên nào hoàn thành khóa học theo bộ lọc hiện tại.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($enrollments->hasPages())
        <div class="card-footer bg-white">
            {{ $enrollments->links() }}
        </div>
    @endif
</div>
@endsection
