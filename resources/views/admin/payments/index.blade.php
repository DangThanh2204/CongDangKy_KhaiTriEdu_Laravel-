@extends('layouts.admin')

@section('title', 'Quáº£n lÃ½ Thanh toÃ¡n')
@section('page-title', 'Quáº£n lÃ½ Thanh toÃ¡n')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">Danh sÃ¡ch thanh toÃ¡n</h4>
                <p class="text-muted mb-0">Quáº£n lÃ½ táº¥t cáº£ giao dá»‹ch há»c phÃ­ trÃªn há»‡ thá»‘ng</p>
            </div>
            <a href="{{ route('admin.payments.export', request()->query(), false) }}" class="btn btn-outline-success">
                <i class="fas fa-file-excel me-2"></i>Xuáº¥t Excel
            </a>
        </div>
    </div>
</div>

<div class="stats-grid mb-4">
    <div class="stat-card users"><div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div><div class="stat-number">{{ $stats['total'] }}</div><div class="stat-label">Tá»•ng giao dá»‹ch</div></div>
    <div class="stat-card courses"><div class="stat-icon"><i class="fas fa-clock"></i></div><div class="stat-number">{{ $stats['pending'] }}</div><div class="stat-label">Chá» xá»­ lÃ½</div></div>
    <div class="stat-card revenue"><div class="stat-icon"><i class="fas fa-check-circle"></i></div><div class="stat-number">{{ $stats['completed'] }}</div><div class="stat-label">HoÃ n thÃ nh</div></div>
    <div class="stat-card orders"><div class="stat-icon"><i class="fas fa-times-circle"></i></div><div class="stat-number">{{ $stats['failed'] }}</div><div class="stat-label">Tháº¥t báº¡i</div></div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label">TÃ¬m kiáº¿m há»c viÃªn</label><input type="text" name="search" class="form-control" placeholder="TÃªn hoáº·c email há»c viÃªn..." value="{{ $search }}"></div>
            <div class="col-md-2"><label class="form-label">MÃ£ giao dá»‹ch</label><input type="text" name="reference" class="form-control" placeholder="TÃ¬m theo mÃ£..." value="{{ $reference ?? '' }}"></div>
            <div class="col-md-2"><label class="form-label">PhÆ°Æ¡ng thá»©c</label><select name="method" class="form-select"><option value="">Táº¥t cáº£</option><option value="wallet" {{ (isset($method) && $method === 'wallet') ? 'selected' : '' }}>VÃ­</option><option value="vnpay" {{ (isset($method) && $method === 'vnpay') ? 'selected' : '' }}>VNPay</option><option value="bank_transfer" {{ (isset($method) && $method === 'bank_transfer') ? 'selected' : '' }}>Chuyá»ƒn khoáº£n</option><option value="cash" {{ (isset($method) && $method === 'cash') ? 'selected' : '' }}>Thanh toÃ¡n trá»±c tiáº¿p</option></select></div>
            <div class="col-md-2"><label class="form-label">Tráº¡ng thÃ¡i</label><select name="status" class="form-select"><option value="">Táº¥t cáº£</option><option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Chá» xá»­ lÃ½</option><option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>HoÃ n thÃ nh</option><option value="failed" {{ $status === 'failed' ? 'selected' : '' }}>Tháº¥t báº¡i</option></select></div>
            <div class="col-md-1"><label class="form-label">Tá»« ngÃ y</label><input type="date" name="from_date" class="form-control" value="{{ $fromDate ?? '' }}"></div>
            <div class="col-md-1"><label class="form-label">Äáº¿n ngÃ y</label><input type="date" name="to_date" class="form-control" value="{{ $toDate ?? '' }}"></div>
            <div class="col-md-1 d-grid"><button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i></button></div>
            <div class="col-md-12"><a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary"><i class="fas fa-refresh me-2"></i>Reset bá»™ lá»c</a></div>
        </form>
    </div>
</div>

<div class="card"><div class="card-body p-0"><div class="table-responsive"><table class="table table-hover mb-0"><thead class="table-light"><tr><th class="ps-4">Há»c viÃªn</th><th>KhÃ³a há»c</th><th>Lá»›p</th><th>Sá»‘ tiá»n</th><th>PhÆ°Æ¡ng thá»©c</th><th>Tráº¡ng thÃ¡i</th><th>NgÃ y thanh toÃ¡n</th><th class="text-end pe-4">Thao tÃ¡c</th></tr></thead><tbody>@forelse($payments as $payment)<tr><td class="ps-4"><div class="d-flex align-items-center"><div class="me-3"><i class="fas fa-user text-primary fs-5"></i></div><div><h6 class="mb-1">{{ $payment->user->fullname }}</h6><small class="text-muted">{{ $payment->user->email }}</small></div></div></td><td><h6 class="mb-1">{{ $payment->course->title ?? 'â€”' }}</h6><small class="text-muted">{{ $payment->course->category->name ?? 'ChÆ°a phÃ¢n loáº¡i' }}</small></td><td>@if($payment->courseClass)<span class="badge bg-secondary">{{ $payment->courseClass->name }}</span>@else<span class="text-muted">--</span>@endif</td><td>{{ number_format($payment->amount, 0, ',', '.') }}â‚«</td><td>{{ $payment->method_label }}</td><td>@php $badge = 'secondary'; if ($payment->status === 'pending') $badge = 'warning'; if ($payment->status === 'completed') $badge = 'success'; if ($payment->status === 'failed') $badge = 'danger'; @endphp <span class="badge bg-{{ $badge }}">{{ ucfirst($payment->status) }}</span></td><td><small class="text-muted">{{ $payment->paid_at ? $payment->paid_at->format('d/m/Y H:i') : $payment->created_at->format('d/m/Y H:i') }}</small></td><td class="text-end pe-4"><div class="btn-group btn-group-sm"><a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-outline-primary" title="Chi tiáº¿t"><i class="fas fa-eye"></i></a>@if($payment->isPending())<form method="POST" action="{{ route('admin.payments.confirm', $payment) }}" class="d-inline">@csrf @method('PATCH')<button type="submit" class="btn btn-outline-success" title="XÃ¡c nháº­n thanh toÃ¡n"><i class="fas fa-check"></i></button></form><button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#failModal{{ $payment->id }}" title="ÄÃ¡nh dáº¥u tháº¥t báº¡i"><i class="fas fa-times"></i></button>@endif</div></td></tr>@if($payment->isPending())<div class="modal fade" id="failModal{{ $payment->id }}" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">ÄÃ¡nh dáº¥u thanh toÃ¡n tháº¥t báº¡i</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><form method="POST" action="{{ route('admin.payments.fail', $payment) }}">@csrf @method('PATCH')<div class="modal-body"><p>Báº¡n Ä‘ang Ä‘Ã¡nh dáº¥u giao dá»‹ch cá»§a <strong>{{ $payment->user->fullname }}</strong> lÃ  <strong>tháº¥t báº¡i</strong>.</p><div class="mb-3"><label for="reason" class="form-label">LÃ½ do (tÃ¹y chá»n)</label><textarea id="reason" name="reason" class="form-control" rows="3"></textarea></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Há»§y</button><button type="submit" class="btn btn-danger">XÃ¡c nháº­n</button></div></form></div></div></div>@endif @empty<tr><td colspan="8" class="text-center py-5"><i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i><h5 class="text-muted">ChÆ°a cÃ³ giao dá»‹ch</h5><p class="text-muted">ChÆ°a cÃ³ thanh toÃ¡n nÃ o Ä‘Æ°á»£c ghi nháº­n trÃªn há»‡ thá»‘ng.</p></td></tr>@endforelse</tbody></table></div></div></div>

@if($payments->hasPages())
<div class="d-flex justify-content-between align-items-center mt-4"><div class="text-muted">Hiá»ƒn thá»‹ {{ $payments->firstItem() }} - {{ $payments->lastItem() }} cá»§a {{ $payments->total() }} giao dá»‹ch</div><div>{{ $payments->links() }}</div></div>
@endif
@endsection
