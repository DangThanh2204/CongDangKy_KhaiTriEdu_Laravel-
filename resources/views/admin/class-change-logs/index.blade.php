@extends('layouts.admin')

@section('title', 'Lịch Sử Đổi Lớp')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-3">
        <div class="col-12">
            <h3 class="mb-0">🔁 Lịch Sử Đổi Lớp</h3>
            <p class="text-muted">Danh sách các lần đổi lớp của học viên</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($logs->count())
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Học viên</th>
                            <th>Khóa</th>
                            <th>Lớp cũ</th>
                            <th>Lớp mới</th>
                            <th>Thời gian</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>
                                @if($log->user)
                                    {{ $log->user->fullname ?? $log->user->username }}<br>
                                    <small class="text-muted">ID: {{ $log->user->id }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($log->enrollment && $log->enrollment->course)
                                    {{ $log->enrollment->course->title ?? '—' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $classes[$log->old_class_id]->name ?? '—' }}</td>
                            <td>{{ $classes[$log->new_class_id]->name ?? '—' }}</td>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->reason ?? '' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
            @else
                <div class="text-center py-4 text-muted">Chưa có bản ghi đổi lớp nào.</div>
            @endif
        </div>
    </div>
</div>
@endsection
