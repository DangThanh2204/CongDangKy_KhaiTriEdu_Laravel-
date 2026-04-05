@extends('layouts.admin')

@section('title', 'Chi tiết đợt học')
@section('page-title', 'Chi tiết đợt học')

@section('content')
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="card-title mb-0">{{ $class->name }}</h5>
            <small class="text-muted">Mã đợt học: #{{ $class->id }}</small>
        </div>
        <div>
            <a href="{{ route('admin.classes.edit', $class) }}" class="btn btn-warning me-2"><i class="fas fa-edit me-1"></i>Chỉnh sửa</a>
            <a href="{{ route('admin.classes.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i>Quay lại</a>
        </div>
    </div>
    <div class="card-body">
        <div class="row g-4">
            <div class="col-lg-7">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Khóa học</dt><dd class="col-sm-8">{{ $class->course?->title ?? 'N/A' }}</dd>
                    <dt class="col-sm-4">Giảng viên</dt><dd class="col-sm-8">{{ $class->instructor?->fullname ?? 'N/A' }}</dd>
                    <dt class="col-sm-4">Thời gian</dt><dd class="col-sm-8">{{ optional($class->start_date)->format('d/m/Y') }} - {{ optional($class->end_date)->format('d/m/Y') }}</dd>
                    <dt class="col-sm-4">Lịch dự kiến</dt><dd class="col-sm-8">{{ $class->schedule_text ?: 'Chưa cập nhật' }}</dd>
                    <dt class="col-sm-4">Địa điểm / link</dt><dd class="col-sm-8">{{ $class->meeting_info ?: 'Chưa cập nhật' }}</dd>
                    <dt class="col-sm-4">Sức chứa</dt><dd class="col-sm-8">{{ $class->max_students > 0 ? $class->max_students : 'Không giới hạn' }}</dd>
                    <dt class="col-sm-4">Trạng thái</dt><dd class="col-sm-8">@if($class->status === 'active')<span class="badge bg-success">Mở đăng ký</span>@else<span class="badge bg-secondary">Tạm dừng</span>@endif</dd>
                </dl>
            </div>
            <div class="col-lg-5">
                <div class="card border"><div class="card-header bg-light"><h6 class="card-title mb-0">Tình hình đăng ký</h6></div><div class="card-body"><div class="d-flex justify-content-between mb-2"><span>Học viên đã đăng ký</span><strong>{{ $class->current_students_count }}</strong></div><div class="d-flex justify-content-between mb-2"><span>Sức chứa</span><strong>{{ $class->max_students > 0 ? $class->max_students : 'Không giới hạn' }}</strong></div><div class="d-flex justify-content-between"><span>Trạng thái</span><span class="badge bg-{{ $class->status_badge }}">{{ $class->status_text }}</span></div></div></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Danh sách học viên đăng ký</h5></div>
    <div class="card-body p-0">
        @if($class->enrollments->isEmpty())
            <div class="p-4 text-center text-muted"><i class="fas fa-user-graduate fa-2x mb-2"></i><p class="mb-0">Chưa có học viên nào đăng ký đợt học này.</p></div>
        @else
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light"><tr><th>Học viên</th><th>Email</th><th>Trạng thái</th><th>Ngày đăng ký</th></tr></thead>
                    <tbody>
                        @foreach($class->enrollments as $enrollment)
                            <tr>
                                <td>{{ $enrollment->student?->fullname ?? 'N/A' }}</td>
                                <td>{{ $enrollment->student?->email ?? 'N/A' }}</td>
                                <td><span class="badge bg-{{ $enrollment->status_color }}">{{ $enrollment->status_text }}</span></td>
                                <td>{{ optional($enrollment->created_at)->format('d/m/Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
