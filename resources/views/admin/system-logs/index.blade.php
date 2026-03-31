@extends('layouts.admin')

@section('title', 'Nhật ký hệ thống')

@section('content')
<div class="container-fluid py-4">
    <div class="card">
        <div class="card-body">
            <h5>Nhật ký hệ thống</h5>

            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
                    <input type="text" name="user" class="form-control" placeholder="Người dùng / email" value="{{ request('user') }}">
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">Tất cả</option>
                        <option value="security" {{ request('category') === 'security' ? 'selected' : '' }}>Bảo mật</option>
                        <option value="transaction" {{ request('category') === 'transaction' ? 'selected' : '' }}>Giao dịch</option>
                        <option value="system" {{ request('category') === 'system' ? 'selected' : '' }}>Hệ thống</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="text" name="action" class="form-control" placeholder="Hành động" value="{{ request('action') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
                <div class="col-md-1">
                    <button class="btn btn-primary w-100">Tìm</button>
                </div>
            </form>

            <div class="mb-3">
                <a href="{{ route('admin.system-logs.export', request()->all()) }}" class="btn btn-outline-secondary">Export CSV</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Người dùng</th>
                            <th>Danh mục</th>
                            <th>Hành động</th>
                            <th>Mã</th>
                            <th>Chi tiết</th>
                            <th>IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $log->user?->fullname ?? '—' }}</td>
                            <td>{{ ucfirst($log->category) }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ $log->reference ?? '—' }}</td>
                            <td><small>{{ is_array($log->details) ? json_encode($log->details, JSON_UNESCAPED_UNICODE) : $log->details }}</small></td>
                            <td>{{ $log->ip ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3">{{ $logs->links() }}</div>
        </div>
    </div>
</div>
@endsection
