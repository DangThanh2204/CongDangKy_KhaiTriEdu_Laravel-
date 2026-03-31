@extends('layouts.admin')

@section('title', 'Giao dịch nạp tiền')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h5 class="mb-1 text-primary">Giao dịch nạp tiền</h5>
                    <div class="text-muted small">Theo dõi yêu cầu nạp trực tiếp, chuyển khoản QR và các giao dịch đã hết hạn ngay tại một màn hình.</div>
                </div>
                <a href="{{ route('admin.wallet-transactions.export', request()->query(), false) }}" class="btn btn-outline-success">
                    <i class="fas fa-file-excel me-2"></i>Xuất Excel
                </a>
            </div>

            <form method="GET" class="wallet-filter-form mb-4">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-xl-4">
                        <label for="search" class="form-label filter-label">Tìm kiếm</label>
                        <input
                            id="search"
                            type="text"
                            name="search"
                            class="form-control filter-control"
                            placeholder="Tìm theo mã giao dịch, tên, email, username"
                            value="{{ request('search') }}">
                    </div>

                    <div class="col-12 col-md-6 col-xl-2">
                        <label for="status" class="form-label filter-label">Trạng thái</label>
                        <select id="status" name="status" class="form-select filter-control">
                            <option value="">Tất cả trạng thái</option>
                            <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                            <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Đã hết hạn</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Hoàn thành</option>
                            <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Thất bại</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-xl-2">
                        <label for="method" class="form-label filter-label">Phương thức</label>
                        <select id="method" name="method" class="form-select filter-control">
                            <option value="">Tất cả phương thức</option>
                            <option value="direct" {{ request('method') === 'direct' ? 'selected' : '' }}>Nạp trực tiếp</option>
                            <option value="qr" {{ request('method') === 'qr' ? 'selected' : '' }}>QR</option>
                            <option value="bank" {{ request('method') === 'bank' ? 'selected' : '' }}>Ngân hàng</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-xl-2">
                        <label for="from_date" class="form-label filter-label">Từ ngày</label>
                        <input
                            id="from_date"
                            type="date"
                            name="from_date"
                            class="form-control filter-control"
                            value="{{ request('from_date') }}">
                    </div>

                    <div class="col-12 col-md-6 col-xl-2">
                        <label for="to_date" class="form-label filter-label">Đến ngày</label>
                        <input
                            id="to_date"
                            type="date"
                            name="to_date"
                            class="form-control filter-control"
                            value="{{ request('to_date') }}">
                    </div>
                </div>

                <div class="filter-actions d-flex flex-wrap gap-2 mt-3">
                    <button type="submit" class="btn btn-primary filter-btn">
                        <i class="fas fa-search me-2"></i>Tìm
                    </button>
                    <a href="{{ route('admin.wallet-transactions.index', [], false) }}" class="btn btn-outline-secondary filter-btn">
                        <i class="fas fa-rotate-left me-2"></i>Reset bộ lọc
                    </a>
                </div>
            </form>

            @if($transactions->count())
                <div class="table-responsive">
                    <table class="table table-striped mt-3 align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Học viên</th>
                                <th>Số tiền</th>
                                <th>Mã giao dịch</th>
                                <th>Phương thức</th>
                                <th>Trạng thái</th>
                                <th>Bắt đầu</th>
                                <th>Hết hạn</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $tx)
                                @php $method = data_get($tx->metadata, 'method', '-'); @endphp
                                <tr>
                                    <td>{{ $tx->id }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $tx->wallet->user->fullname ?? $tx->wallet->user->username }}</div>
                                        <div class="small text-muted">{{ $tx->wallet->user->email ?? $tx->wallet->user->username }}</div>
                                    </td>
                                    <td>{{ number_format($tx->amount, 0) }}đ</td>
                                    <td><code>{{ $tx->reference }}</code></td>
                                    <td>
                                        <span class="badge {{ $method === 'direct' ? 'bg-primary' : 'bg-secondary' }}">
                                            {{ strtoupper($method) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge {{ $tx->status_badge_class }}">{{ $tx->status_label }}</span>
                                        </div>
                                        @if($tx->isDirectTopup() && $tx->expiry_notice)
                                            <div class="small text-muted mt-1">{{ $tx->expiry_notice }}</div>
                                        @endif
                                    </td>
                                    <td>{{ $tx->requested_at_label ?? '—' }}</td>
                                    <td>
                                        @if($tx->status === 'expired')
                                            {{ $tx->expired_at_label ?? $tx->expires_at_label ?? '—' }}
                                        @else
                                            {{ $tx->expires_at_label ?? '—' }}
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('admin.wallet-transactions.show', $tx, false) }}" class="btn btn-sm btn-outline-primary">Xem</a>
                                        @if($tx->isPending())
                                            <form method="POST" action="{{ route('admin.wallet-transactions.confirm', $tx, false) }}" class="d-inline">
                                                @csrf
                                                @method('PATCH')
                                                <button class="btn btn-sm btn-success" onclick="return confirm('Xác nhận đã nhận tiền cho mã giao dịch {{ $tx->reference }}?')">Xác nhận</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $transactions->links() }}</div>
            @else
                <div class="text-muted py-3">Không có giao dịch nào phù hợp với điều kiện tìm kiếm.</div>
            @endif
        </div>
    </div>
</div>

<style>
    .wallet-filter-form .filter-label {
        margin-bottom: 0.55rem;
        font-size: 0.85rem;
        font-weight: 600;
        color: #6c757d;
    }

    .wallet-filter-form .filter-control {
        min-height: 60px;
        border-radius: 16px;
        padding: 0.85rem 1.1rem;
        border: 1px solid #d8e0ea;
        box-shadow: none;
    }

    .wallet-filter-form .filter-control:focus {
        border-color: rgba(13, 110, 253, 0.4);
        box-shadow: 0 0 0 0.18rem rgba(13, 110, 253, 0.12);
    }

    .wallet-filter-form .filter-btn {
        min-width: 180px;
        min-height: 50px;
        border-radius: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    @media (max-width: 767.98px) {
        .wallet-filter-form .filter-btn {
            width: 100%;
        }
    }
</style>
@endsection