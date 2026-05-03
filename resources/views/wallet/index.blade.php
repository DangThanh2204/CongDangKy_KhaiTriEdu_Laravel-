@extends('layouts.app')

@section('title', 'Ví của tôi')

@section('content')
@php
    $selectedMethod = old('method', $supportsVnpayTopup ? 'vnpay' : ($supportsBankTransferTopup ? 'bank' : 'direct'));
    $bankSnapshot = data_get($bankRequest?->metadata, 'bank_transfer', $bankTransferConfig);
    $bankTransferContent = data_get($bankRequest?->metadata, 'transfer_content', $bankRequest?->reference);
    $bankAdminNote = data_get($bankRequest?->metadata, 'admin_note');
@endphp

<div class="container py-5">
    <div class="row">
        <div class="col-lg-10 mx-auto">
            <div class="card mb-4 shadow-sm border-0 wallet-balance-card">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <div class="text-white-50 small mb-1">Ví của tôi</div>
                            <h2 class="text-white fw-bold mb-1">Số dư hiện tại</h2>
                            <div class="display-5 fw-bold text-white">{{ number_format($wallet->balance, 0) }}đ</div>
                        </div>
                        <div class="wallet-balance-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <h5 class="fw-bold mb-3"><i class="fas fa-coins text-warning me-2"></i>Nạp tiền vào ví</h5>

                    <form action="{{ route('wallet.topup') }}" method="POST" id="wallet-topup-form">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Phương thức nạp tiền</label>
                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="method" id="method_direct" value="direct" {{ $selectedMethod === 'direct' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="method_direct">Nạp trực tiếp tại quầy</label>
                                </div>

                                @if($supportsBankTransferTopup)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="method" id="method_bank" value="bank" {{ $selectedMethod === 'bank' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="method_bank">Chuyển khoản ngân hàng</label>
                                    </div>
                                @endif

                                @if($supportsVnpayTopup)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="method" id="method_vnpay" value="vnpay" {{ $selectedMethod === 'vnpay' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="method_vnpay">VNPay</label>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="amount" class="form-label fw-semibold">Số tiền nạp (VNĐ)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light"><i class="fas fa-money-bill-wave text-success"></i></span>
                                <input type="number" name="amount" id="amount" class="form-control" min="1000" max="10000000" value="{{ old('amount') }}" required placeholder="Ví dụ: 100000">
                                <button type="submit" class="btn btn-primary px-4" id="wallet-submit-button">
                                    <i class="fas fa-arrow-right me-1"></i>Tạo yêu cầu
                                </button>
                            </div>
                            <small class="text-muted">Số tiền tối thiểu 1.000đ, tối đa 10.000.000đ.</small>
                            @error('amount')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div id="direct-instructions" style="display:none;">
                            <div class="alert alert-warning mb-0">
                                <h5 class="mb-2">Nạp trực tiếp tại quầy</h5>
                                <p class="mb-2">Hệ thống sẽ tạo một mã nạp tiền. Bạn mang mã đó tới quầy thu hộ hoặc trung tâm để nhân viên xác nhận.</p>
                                <p class="mb-0">Sau khi nhân viên thu tiền và admin duyệt, số dư sẽ được cộng vào ví của bạn.</p>
                            </div>
                        </div>

                        <div id="bank-instructions" style="display:none;">
                            <div class="alert alert-info mb-0">
                                <h5 class="mb-2">Chuyển khoản ngân hàng</h5>
                                <p class="mb-2">Sau khi tạo yêu cầu, hệ thống sẽ khóa sẵn nội dung chuyển khoản theo mã riêng của bạn để admin đối soát đúng tài khoản.</p>
                                <p class="mb-0">Bạn chuyển khoản xong rồi quay lại nhấn <strong>Tôi đã chuyển khoản xong</strong>. Hệ thống vẫn chờ admin duyệt trước khi cộng tiền.</p>
                            </div>
                        </div>

                        <div id="vnpay-instructions" style="display:none;">
                            <div class="alert alert-primary mb-0">
                                <h5 class="mb-2">Nạp tiền qua VNPay</h5>
                                <p class="mb-2">Sau khi tạo yêu cầu, hệ thống sẽ chuyển bạn sang cổng VNPay để quét QR bằng app ngân hàng hoặc chọn phương thức thanh toán phù hợp.</p>
                                <p class="mb-0">Mã QR của VNPay sẽ hiển thị ở cổng thanh toán VNPay, không hiển thị trực tiếp tại trang ví này.</p>
                            </div>
                        </div>
                    </form>

                    @if(! $supportsBankTransferTopup)
                        <div class="alert alert-light border mt-4 mb-0">
                            <strong>Chuyển khoản ngân hàng:</strong> admin chưa cấu hình đầy đủ tên ngân hàng, chủ tài khoản hoặc số tài khoản nhận tiền.
                        </div>
                    @endif
                </div>
            </div>

            @if($directRequest)
                <div class="card mb-4 shadow-sm border-0 {{ $directRequest->status === 'expired' ? 'bg-light' : '' }}">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">Mã nạp trực tiếp</h5>
                                <p class="text-muted mb-0">Đưa mã này cho quầy thu hộ hoặc nhân viên để xác nhận nạp tiền thủ công.</p>
                            </div>
                            <span class="badge {{ $directRequest->status_badge_class }}">{{ $directRequest->status_label }}</span>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="small text-muted mb-1">Mã thanh toán</div>
                                    <div class="fw-bold fs-5">{{ $directRequest->reference }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="small text-muted mb-1">Số tiền</div>
                                    <div class="fw-semibold">{{ number_format($directRequest->amount, 0) }}đ</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="small text-muted mb-1">Bắt đầu yêu cầu</div>
                                    <div class="fw-semibold">{{ $directRequest->requested_at_label }}</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-3 h-100">
                                    <div class="small text-muted mb-1">Hết hạn</div>
                                    <div class="fw-semibold text-danger">{{ $directRequest->expires_at_label ?? 'Chưa xác định' }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="alert {{ $directRequest->status === 'expired' ? 'alert-secondary' : 'alert-warning' }} mb-3">
                            {{ $directRequest->expiry_notice ?? 'Mã này đang chờ admin xác nhận.' }}
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ $directRequest->reference }}').then(() => alert('Đã sao chép mã thanh toán'))">Sao chép mã</button>
                            <a href="#wallet-topup-form" class="btn btn-outline-primary">Tạo yêu cầu mới</a>
                        </div>
                    </div>
                </div>
            @endif

            @if($bankRequest)
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                            <div>
                                <h5 class="card-title mb-1">Yêu cầu chuyển khoản ngân hàng</h5>
                                <p class="text-muted mb-0">Chuyển đúng số tiền, đúng nội dung để admin đối soát và cộng vào ví.</p>
                            </div>
                            <span class="badge {{ $bankRequest->status_badge_class }}">{{ $bankRequest->status_label }}</span>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-lg-7">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <div class="small text-muted mb-1">Ngân hàng</div>
                                            <div class="fw-semibold">{{ data_get($bankSnapshot, 'bank_name', 'Chưa cấu hình') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <div class="small text-muted mb-1">Chủ tài khoản</div>
                                            <div class="fw-semibold">{{ data_get($bankSnapshot, 'account_name', 'Chưa cấu hình') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <div class="small text-muted mb-1">Số tài khoản</div>
                                            <div class="fw-semibold">{{ data_get($bankSnapshot, 'account_number', 'Chưa cấu hình') }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <div class="small text-muted mb-1">Nội dung chuyển khoản</div>
                                            <div class="fw-semibold">{{ $bankTransferContent }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <div class="small text-muted mb-1">Số tiền</div>
                                            <div class="fw-semibold">{{ number_format($bankRequest->amount, 0) }}đ</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="border rounded p-3 h-100">
                                            <div class="small text-muted mb-1">Mã yêu cầu</div>
                                            <div class="fw-semibold">{{ $bankRequest->reference }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="border rounded p-3 text-center h-100">
                                    <div class="small text-muted mb-2">QR chuyển khoản cố định</div>
                                    @if($bankRequestQrUrl)
                                        <img src="{{ $bankRequestQrUrl }}" alt="QR chuyển khoản" class="img-fluid rounded border mb-2" style="max-height: 260px;">
                                        <div class="small text-muted">QR đã khóa sẵn số tiền, tài khoản và nội dung chuyển khoản.</div>
                                    @else
                                        <div class="alert alert-light border mb-0">Chưa tạo được QR tự động vì thiếu mã BIN ngân hàng.</div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($bankAdminNote)
                            <div class="alert alert-danger">Ghi chú từ admin: {{ $bankAdminNote }}</div>
                        @endif

                        @if($bankRequest->isPending())
                            <form action="{{ route('wallet.confirm-qr') }}" method="POST" class="d-flex flex-wrap gap-2 align-items-center">
                                @csrf
                                <input type="hidden" name="token" value="{{ $bankRequest->reference }}">
                                <button type="submit" class="btn btn-success">Tôi đã chuyển khoản xong</button>
                                <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ $bankTransferContent }}').then(() => alert('Đã sao chép nội dung chuyển khoản'))">Sao chép nội dung</button>
                            </form>
                        @else
                            <div class="alert alert-light border mb-0">Yêu cầu này đã được xử lý. Bạn có thể tạo yêu cầu mới nếu cần nạp thêm tiền.</div>
                        @endif
                    </div>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <h4 class="card-title mb-4">Lịch sử giao dịch</h4>

                    @if($transactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Ngày</th>
                                        <th>Loại</th>
                                        <th>Phương thức</th>
                                        <th>Số tiền</th>
                                        <th>Mã giao dịch</th>
                                        <th>Trạng thái</th>
                                        <th>Khóa học</th>
                                        <th>Ghi chú</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $tx)
                                        @php
                                            $note = $tx->expiry_notice
                                                ?? data_get($tx->metadata, 'admin_note')
                                                ?? data_get($tx->metadata, 'failed_reason')
                                                ?? '—';
                                        @endphp
                                        <tr>
                                            <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-capitalize">{{ $tx->type }}</td>
                                            <td>{{ $tx->method_label }}</td>
                                            <td>{{ number_format($tx->amount, 0) }}đ</td>
                                            <td>
                                                @if($tx->reference)
                                                    <div class="d-flex flex-wrap align-items-center gap-2">
                                                        <code>{{ $tx->reference }}</code>
                                                        <button class="btn btn-sm btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ $tx->reference }}').then(() => alert('Đã sao chép mã giao dịch'))">Sao chép</button>
                                                    </div>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td><span class="badge {{ $tx->status_badge_class }}">{{ $tx->status_label }}</span></td>
                                            <td>
                                                @if($tx->course)
                                                    <a href="{{ route('courses.show', $tx->course) }}">{{ $tx->course->title ?? '—' }}</a>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td>{{ $note }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        {{ $transactions->links() }}
                    @else
                        <div class="alert alert-info mb-0">Chưa có giao dịch nào.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const methodInputs = document.querySelectorAll('input[name="method"]');
        const directInstructions = document.getElementById('direct-instructions');
        const bankInstructions = document.getElementById('bank-instructions');
        const vnpayInstructions = document.getElementById('vnpay-instructions');
        const amountInput = document.getElementById('amount');
        const submitButton = document.getElementById('wallet-submit-button');

        amountInput?.addEventListener('blur', function () {
            const value = parseFloat(this.value);

            if (value < 1000 && value > 0) {
                this.value = 1000;
                alert('Số tiền tối thiểu là 1.000đ');
            } else if (value > 10000000) {
                this.value = 10000000;
                alert('Số tiền tối đa là 10.000.000đ');
            }
        });

        function refreshMethodUI() {
            const selected = document.querySelector('input[name="method"]:checked')?.value;

            if (directInstructions) {
                directInstructions.style.display = selected === 'direct' ? 'block' : 'none';
            }

            if (bankInstructions) {
                bankInstructions.style.display = selected === 'bank' ? 'block' : 'none';
            }

            if (vnpayInstructions) {
                vnpayInstructions.style.display = selected === 'vnpay' ? 'block' : 'none';
            }

            if (submitButton) {
                submitButton.innerHTML = selected === 'vnpay'
                    ? '<i class="fas fa-arrow-right me-1"></i>Tiếp tục tới VNPay'
                    : '<i class="fas fa-arrow-right me-1"></i>Tạo yêu cầu';
            }
        }

        methodInputs.forEach((input) => input.addEventListener('change', refreshMethodUI));
        refreshMethodUI();
    });
</script>
@endpush
@endsection
