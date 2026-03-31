@extends('layouts.admin')

@section('title', 'Quản lý Thanh toán')
@section('page-title', 'Quản lý Thanh toán')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Danh sách thanh toán</h4>
                <p class="text-muted mb-0">Quản lý tất cả giao dịch học phí trên hệ thống</p>
            </div>
            <a href="{{ route('admin.payments.export', request()->query(), false) }}" class="btn btn-outline-success">
                <i class="fas fa-file-excel me-2"></i>Xuáº¥t Excel
            </a>
        </div>
    </div>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card users"><div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div><div class="stat-number">{{ $stats['total'] }}</div><div class="stat-label">Tổng giao dịch</div></div>
    <div class="stat-card courses"><div class="stat-icon"><i class="fas fa-clock"></i></div><div class="stat-number">{{ $stats['pending'] }}</div><div class="stat-label">Chờ xử lý</div></div>
    <div class="stat-card revenue"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div class="stat-number">{{ $stats['completed'] }}</div><div class="stat-label">Hoàn thành</div></div>
    <div class="stat-card orders"><div class="stat-icon"><i class="fas fa-times-circle"></i></div><div class="stat-number">{{ $stats['failed'] }}</div><div class="stat-label">Tháº¥t báº¡i</div></div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">Tìm kiếm học viên</label><input type="text" name="search" class="form-control" placeholder="Tên hoặc email học viên..." value="{{ $search }}"></div>
            <div class="col-md-2"><label class="form-label">Mã giao dịch</label><input type="text" name="reference" class="form-control" placeholder="Tìm theo mã..." value="{{ $reference ?? '' }}"></div>
            <div class="col-md-2"><label class="form-label">Phương thức</label><select name="method" class="form-select"><option value="">Tất cả</option><option value="wallet" {{ (isset($method) && $method === 'wallet') ? 'selected' : '' }}>Ví</option><option value="vnpay" {{ (isset($method) && $method === 'vnpay') ? 'selected' : '' }}>VNPay</option><option value="bank_transfer" {{ (isset($method) && $method === 'bank_transfer') ? 'selected' : '' }}>Chuyển khoản</option><option value="cash" {{ (isset($method) && $method === 'cash') ? 'selected' : '' }}>Thanh toán trực tiếp</option></select></div>
            <div class="col-md-2"><label class="form-label">Trạng thái</label><select name="status" class="form-select"><option value="">Tất cả</option><option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Chờ xử lý</option><option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Hoàn thành</option><option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Thất bại</option></select></div>
            <div class="col-md-1"><label class="form-label">Từ ngày</label><input type="date" name="from_date" class="form-control" value="{{ $fromDate ?? '' }}"></div>
            <div class="col-md-1"><label class="form-label">Đến ngày</label><input type="date" name="to_date" class="form-control" value="{{ $toDate ?? '' }}"></div>
            <div class="col-md-1 d-grid"><button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button></div>
            <div class="col-md-12"><a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary"><i class="fas fa-refresh me-2"></i>Reset bộ lọc</a></div>
        </form>
    </div>
</div>

<div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th class="ps-4">Học viên</th><th>Khóa học</th><th>Lớp</th><th>Số tiền</th><th>Phương thức</th><th>Trạng thái</th><th>Ngày thanh toán</th><th class="text-end pe-4">Thao tác</th></tr></thead><tbody>@forelse($payments as $payment)<tr><td class="ps-4"><div class="d-flex align-items-center"><div class="me-3"><i class="fas fa-user text-primary fs-5"></i></div><div><h6 class="mb-1">{{ $payment->user->fullname }}</h6><small class="text-muted">{{ $payment->user->email }}</small></div></div></td><td><h6 class="mb-1">{{ $payment->course->title ?? '—' }}</h6><small class="text-muted">{{ $payment->course->category->name ?? 'Chưa phân loại' }}</small></td><td>@if($payment->courseClass)<span class="badge bg-secondary">{{ $payment->courseClass->name }}</span>@else<span class="text-muted">--</span>@endif</td><td>{{ number_format($payment->amount, 0, ',', '.') }}₫</td><td>{{ $payment->method_label }}</td><td>@php $badge = 'secondary'; if ($payment->status === 'pending') $badge = 'warning'; if ($payment->status === 'completed') $badge = 'success'; if ($payment->status === 'failed') $badge = 'danger'; @endphp <span class="badge bg-{{ $badge }}">{{ ucfirst($payment->status) }}</span></td><td><small class="text-muted">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : $payment->created_at->format('d/m/Y H:i') }}</small></td><td class="text-end pe-4"><div class="btn-group btn-group-sm"><a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-outline-primary" title="Chi tiết"><i class="fas fa-eye"></i></a>@if($payment->isPending())<form method="POST" action="{{ route('admin.payments.confirm', $payment) }}" class="d-inline">@csrf @method('PATCH')<button type="submit" class="btn btn-outline-success" title="Xác nhận thanh toán"><i class="fas fa-check"></i></button></form><button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#failModal{{ $payment->id }}" title="Đánh dấu thất bại"><i class="fas fa-times"></i></button>@endif</div></td></tr>@if($payment->isPending())<div class="modal fade" id="failModal{{ $payment->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Đánh dấu thanh toán thất bại</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('admin.payments.fail', $payment) }}">@csrf @method('PATCH')<div class="modal-body"><p>Bạn đang đánh dấu giao dịch của <strong>{{ $payment->user->fullname }}</strong> là <strong>thất bại</strong>.</p><div class="mb-3"><label for="reason" class="form-label">Lý do (tùy chọn)</label><textarea id="reason" name="reason" class="form-control" rows="3"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button><button type="submit" class="btn btn-danger">Xác nhận</button></div></form></div></div></div>@endif @empty<tr><td colspan="8" class="text-center py-5"><i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i><h5 class="text-muted">Chưa có giao dịch</h5><p class="text-muted">Chưa có thanh toán nào được ghi nhận trên hệ thống.</p></td></tr>@endforelse</tbody></table></div></div></div>

@if($payments->hasPages())
<div class="d-flex justify-content-between align-items-center mt-4"><div class="text-muted">Hiển thị {{ $payments->firstItem() }} - {{ $payments->lastItem() }} của {{ $payments->total() }} giao dịch</div><div>{{ $payments->links() }}</div></div>
@endif
@endsection
