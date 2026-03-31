@extends('layouts.admin')

@section('title', 'Chi tiÃ¡ÂºÂ¿t Thanh toÃƒÂ¡n')
@section('page-title', 'Chi tiÃ¡ÂºÂ¿t Thanh toÃƒÂ¡n')

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Chi tiÃ¡ÂºÂ¿t thanh toÃƒÂ¡n</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">MÃƒÂ£ giao dÃ¡Â»â€¹ch</p>
                        <h6>{{ $payment->id }}</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">TrÃ¡ÂºÂ¡ng thÃƒÂ¡i</p>
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
                        <p class="text-muted mb-1">HÃ¡Â»Âc viÃƒÂªn</p>
                        <h6>{{ $payment->user->fullname }}</h6>
                        <small class="text-muted">{{ $payment->user->email }}</small>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">KhÃƒÂ³a hÃ¡Â»Âc</p>
                        <h6>{{ $payment->course->title ?? '--' }}</h6>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">LÃ¡Â»â€ºp hÃ¡Â»Âc</p>
                        @if($payment->courseClass)
                            <h6>{{ $payment->courseClass->name }}</h6>
                        @else
                            <span class="text-muted">--</span>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">PhÃ†Â°Ã†Â¡ng thÃ¡Â»Â©c</p>
                        <h6>{{ $payment->method_label }}</h6>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p class="text-muted mb-1">SÃ¡Â»â€˜ tiÃ¡Â»Ân</p>
                        <h6>{{ number_format($payment->amount, 0, ',', '.') }}Ã¢â€šÂ«</h6>
                    </div>
                    <div class="col-md-6">
                        <p class="text-muted mb-1">NgÃƒÂ y thanh toÃƒÂ¡n</p>
                        <h6>{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : ($payment->created_at->format('d/m/Y H:i')) }}</h6>
                    </div>
                </div>

                <div class="mb-3">
                    <p class="text-muted mb-1">Ghi chÃƒÂº</p>
                    <p>{{ $payment->notes ?? '--' }}</p>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Quay lÃ¡ÂºÂ¡i danh sÃƒÂ¡ch
                    </a>

                    @if($payment->isPending())
                    <div class="btn-group">
                        <form method="POST" action="{{ route('admin.payments.confirm', $payment) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-check me-2"></i>XÃƒÂ¡c nhÃ¡ÂºÂ­n
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#failModal">
                            <i class="fas fa-times me-2"></i>ThÃ¡ÂºÂ¥t bÃ¡ÂºÂ¡i
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($payment->isPending())
<!-- Modal Ã„â€˜Ã¡Â»Æ’ Ã„â€˜ÃƒÂ¡nh dÃ¡ÂºÂ¥u thÃ¡ÂºÂ¥t bÃ¡ÂºÂ¡i -->
<div class="modal fade" id="failModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ã„ÂÃƒÂ¡nh dÃ¡ÂºÂ¥u thanh toÃƒÂ¡n thÃ¡ÂºÂ¥t bÃ¡ÂºÂ¡i</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('admin.payments.fail', $payment) }}">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reason" class="form-label">LÃƒÂ½ do (tuÃ¡Â»Â³ chÃ¡Â»Ân)</label>
                        <textarea id="reason" name="reason" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">HÃ¡Â»Â§y</button>
                    <button type="submit" class="btn btn-danger">XÃƒÂ¡c nhÃ¡ÂºÂ­n</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
