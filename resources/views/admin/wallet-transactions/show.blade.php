@extends('layouts.admin')

@section('title', 'Chi tiết giao dịch nạp tiền')

@section('content')
@php
    $method = strtoupper(data_get($walletTransaction->metadata, 'method', '-'));
@endphp

<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between gap-3 mb-4">
                <div>
                    <h5 class="mb-1">Giao dịch #{{ $walletTransaction->id }}</h5>
                    <div class="text-muted">Đối chiếu đúng mã giao dịch trước khi xác nhận tiền mặt cho học viên.</div>
                </div>
                <div>
                    <span class="badge {{ $walletTransaction->status_badge_class }}">{{ $walletTransaction->status_label }}</span>
                </div>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100">
                        <div class="small text-muted mb-1">Mã giao dịch</div>
                        <div class="fs-4 fw-bold"><code>{{ $walletTransaction->reference }}</code></div>

                        <div class="small text-muted mt-3 mb-1">Số tiền</div>
                        <div class="fw-semibold">{{ number_format($walletTransaction->amount, 0) }}₫</div>

                        <div class="small text-muted mt-3 mb-1">Phương thức</div>
                        <div>{{ $method }}</div>

                        <div class="small text-muted mt-3 mb-1">Bắt đầu yêu cầu</div>
                        <div>{{ $walletTransaction->requested_at_label ?? '—' }}</div>

                        <div class="small text-muted mt-3 mb-1">Hết hạn</div>
                        <div>{{ $walletTransaction->expires_at_label ?? 'Không áp dụng' }}</div>

                        @if($walletTransaction->status === 'expired')
                            <div class="small text-muted mt-3 mb-1">Đã hết hạn lúc</div>
                            <div>{{ $walletTransaction->expired_at_label ?? '—' }}</div>
                        @endif
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="border rounded p-3 h-100">
                        <div class="small text-muted mb-1">Học viên</div>
                        <div class="fw-semibold">{{ $walletTransaction->wallet->user->fullname ?? $walletTransaction->wallet->user->username }}</div>
                        <div>{{ $walletTransaction->wallet->user->email }}</div>

                        <div class="small text-muted mt-3 mb-1">Username</div>
                        <div>{{ $walletTransaction->wallet->user->username }}</div>

                        <div class="small text-muted mt-3 mb-1">Ghi chú xử lý</div>
                        <div>{{ data_get($walletTransaction->metadata, 'admin_note', 'Chưa có ghi chú.') }}</div>
                    </div>
                </div>
            </div>

            @if($walletTransaction->status === 'expired')
                <div class="alert alert-secondary mb-4">
                    Yêu cầu nạp trực tiếp này {{ strtolower($walletTransaction->expiry_notice ?? 'đã hết hạn') }}. Hệ thống sẽ không tính đây là giao dịch chờ xử lý nữa.
                </div>
            @elseif($walletTransaction->isPending() && $walletTransaction->isDirectTopup())
                <div class="alert alert-warning mb-4">
                    Giao dịch này đang chờ xác nhận tiền mặt. Vui lòng hoàn tất trước <strong>{{ $walletTransaction->expires_at_label }}</strong> để tránh bị hết hạn tự động.
                </div>
            @endif

            @if($walletTransaction->isPending())
                <div class="row g-3">
                    <div class="col-lg-6">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="text-success">Xác nhận thành công</h6>
                                <p class="text-muted small">Dùng khi admin đã nhận tiền và đối chiếu đúng mã giao dịch học viên cung cấp.</p>

                                <form method="POST" action="{{ route('admin.wallet-transactions.confirm', $walletTransaction, false) }}">
                                    @csrf
                                    @method('PATCH')

                                    <div class="mb-3">
                                        <label class="form-label">Ghi chú admin</label>
                                        <textarea name="admin_note" class="form-control" rows="3" placeholder="Ví dụ: Đã thu tiền tại quầy, đối chiếu mã giao dịch chính xác.">{{ old('admin_note') }}</textarea>
                                    </div>

                                    <button class="btn btn-success" onclick="return confirm('Xác nhận đã nhận tiền cho mã {{ $walletTransaction->reference }}?')">Đã nhận tiền - Xác nhận</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card border-danger">
                            <div class="card-body">
                                <h6 class="text-danger">Đánh dấu thất bại</h6>
                                <p class="text-muted small">Dùng khi học viên chưa nộp tiền, sai mã giao dịch, hoặc admin cần hủy yêu cầu này.</p>

                                <form method="POST" action="{{ route('admin.wallet-transactions.fail', $walletTransaction, false) }}">
                                    @csrf
                                    @method('PATCH')

                                    <div class="mb-3">
                                        <label class="form-label">Lý do</label>
                                        <textarea name="admin_note" class="form-control" rows="3" required placeholder="Ví dụ: Học viên không cung cấp đúng mã giao dịch hoặc chưa thanh toán.">{{ old('admin_note') }}</textarea>
                                    </div>

                                    <button class="btn btn-danger" onclick="return confirm('Đánh dấu thất bại cho mã {{ $walletTransaction->reference }}?')">Đánh dấu thất bại</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="alert alert-secondary mb-0">
                    Giao dịch này đã được xử lý hoặc đã hết hạn. Bạn vẫn có thể tra cứu lại bằng mã <strong>{{ $walletTransaction->reference }}</strong>.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
