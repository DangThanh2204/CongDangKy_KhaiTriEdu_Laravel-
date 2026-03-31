@extends('layouts.admin')

@section('title', 'Chi tiết Thanh toán')
@section('page-title', 'Chi tiết Thanh toán')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Chi tiết thanh toán</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Mã giao dịch</p>
                        <h6>{{ $payment->id }}</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Trạng thái</p>
                        @php
                            $badge = 'secondary';
                            if ($payment->status === 'pending') $badge = 'warning';
                            if ($payment->status === 'completed') $badge = 'success';
                            if ($payment->status === 'failed') $badge = 'danger';
                        @endphp
                        <span class="badge bg-{{ $badge }}">{{ ucfirst($payment->status) }}</span>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Học viên</p>
                        <h6>{{ $payment->user->fullname }}</h6>
                        <small class="text-muted">{{ $payment->user->email }}</small>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Khóa học</p>
                        <h6>{{ $payment->course->title ?? '--' }}</h6>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Lớp học</p>
                        @if($payment->courseClass)
                            <h6>{{ $payment->courseClass->name }}</h6>
                        @else
                            <span class="text-muted">--</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Phương thức</p>
                        <h6>{{ $payment->method_label }}</h6>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Số tiền</p>
                        <h6>{{ number_format($payment->amount, 0, ',', '.') }}â‚«</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">Ngày thanh toán</p>
                        <h6>{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : ($payment->created_at->format('d/m/Y H:i')) }}</h6>
                    </div>
                </div>

                <div class="mb-3">
                    <p class="text-muted mb-1">Ghi chú</p>
                    <p>{{ $payment->notes ?? '--' }}</p>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lại danh sách
                    </a>

                    @if($payment->isPending())
                    <div class="btn-group">
                        <form method="POST" action="{{ route('admin.payments.confirm', $payment) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>Xác nhận
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#failModal">
                            <i class="fas fa-times me-2"></i>Tháº¥t báº¡i
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($payment->isPending())
<!-- Modal để đánh dấu thất bại -->
<div class="modal fade" id="failModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Đánh dấu thanh toán thất bại</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.payments.fail', $payment) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Lý do (tuỳ chọn)</label>
                        <textarea id="reason" name="reason" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Xác nhận</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
