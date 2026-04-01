@extends('layouts.app')

@section('title', 'Ví của tôi')

@section('content')
<div class="container py-5">
    <div class="row">
        <div class="col-lg-9 mx-auto">
            <div class="card mb-4 shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                        <div>
                            <h2 class="card-title mb-2">Ví của tôi</h2>
                            <p class="text-muted mb-0">Số dư hiện tại: <strong>{{ number_format($wallet->balance, 0) }}₫</strong></p>
                        </div>
                        <form action="{{ route('wallet.sync') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">Đồng bộ với FireFly</button>
                        </form>
                    </div>

                    <form action="{{ route('wallet.topup') }}" method="POST" class="row g-3" id="wallet-topup-form">
                        @csrf

                        <div class="col-12">
                            <label class="form-label">Phương thức nạp tiền</label>
                            <div class="d-flex flex-wrap gap-3">
                                @php $selectedMethod = old('method', session('payment_method', $supportsVnpayTopup ? 'vnpay' : 'direct')); @endphp
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="method" id="method_direct" value="direct" {{ $selectedMethod === 'direct' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="method_direct">Nạp trực tiếp</label>
                                </div>
                                @if($supportsVnpayTopup)
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="method" id="method_vnpay" value="vnpay" {{ $selectedMethod === 'vnpay' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="method_vnpay">VNPay</label>
                                    </div>
                                @endif
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="method" id="method_qr" value="qr" {{ $selectedMethod === 'qr' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="method_qr">QR nội bộ</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="method" id="method_bank" value="bank" {{ $selectedMethod === 'bank' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="method_bank">Chuyển khoản ngân hàng</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label for="amount" class="form-label">Số tiền nạp (VNĐ)</label>
                            <input type="number" name="amount" id="amount" class="form-control" min="1000" max="10000000" value="{{ old('amount') }}" required placeholder="Ví dụ: 100000">
                            <small class="text-muted">Số tiền tối thiểu 1.000₫, tối đa 10.000.000₫</small>
                            @error('amount')
                                <div class="text-danger mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100" id="wallet-submit-button">Tạo yêu cầu nạp tiền</button>
                        </div>

                        <div class="col-12" id="vnpay-instructions" style="display: none;">
                            <div class="alert alert-primary mt-3 mb-0">
                                <h5 class="mb-2">Thanh toán qua VNPay</h5>
                                <p class="mb-2">Sau khi tạo yêu cầu, hệ thống sẽ chuyển bạn sang cổng VNPay để quét QR bằng app ngân hàng hoặc chọn phương thức thanh toán phù hợp.</p>
                                <p class="mb-0">QR của VNPay không hiển thị trực tiếp tại trang ví này. Mã QR sẽ xuất hiện ở trang cổng thanh toán của VNPay.</p>
                            </div>
                        </div>

                        <div class="col-12" id="bank-instructions" style="display: none;">
                            <div class="alert alert-info mt-3 mb-0">
                                <h5 class="mb-2">Thông tin chuyển khoản</h5>
                                <p>Vui lòng chuyển khoản vào tài khoản bên dưới:</p>
                                <ul class="mb-3">
                                    <li><strong>Ngân hàng:</strong> Vietcombank</li>
                                    <li><strong>Chủ tài khoản:</strong> Nguyễn Văn A</li>
                                    <li><strong>Số tài khoản:</strong> 123456789</li>
                                </ul>
                                <p class="mb-2">Trong nội dung chuyển khoản, ghi <strong>mã xác nhận</strong>: <span id="bank-reference">-</span></p>
                                <p class="mb-0">Sau khi chuyển xong, quay lại trang này và nhấn <strong>Tôi đã chuyển xong</strong> để cập nhật số dư.</p>
                            </div>
                        </div>
                    </form>

                    @if($directRequest)
                        <div class="card mt-4 border-0 shadow-sm {{ $directRequest->status === 'expired' ? 'bg-light' : '' }}">
                            <div class="card-body">
                                @if($directRequest->status === 'expired')
                                    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title text-danger mb-1">Yêu cầu nạp trực tiếp đã hết hạn</h5>
                                            <p class="text-muted mb-0">Mã này không còn hiệu lực để nộp tiền tại quầy.</p>
                                        </div>
                                        <span class="badge {{ $directRequest->status_badge_class }}">{{ $directRequest->status_label }}</span>
                                    </div>

                                    <div class="row g-3 mb-3">
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <div class="small text-muted mb-1">Mã thanh toán</div>
                                                <div class="fw-bold fs-5">{{ $directRequest->reference }}</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <div class="small text-muted mb-1">Số tiền</div>
                                                <div class="fw-semibold">{{ number_format($directRequest->amount, 0) }}₫</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="border rounded p-3 h-100">
                                                <div class="small text-muted mb-1">Bắt đầu yêu cầu</div>
                                                <div class="fw-semibold">{{ $directRequest->requested_at_label }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="alert alert-secondary mb-0">
                                        Yêu cầu này {{ strtolower($directRequest->expiry_notice ?? 'đã hết hạn') }}. Vui lòng tạo yêu cầu nạp mới nếu bạn vẫn muốn nạp trực tiếp.
                                    </div>
                                @else
                                    <div class="d-flex flex-wrap justify-content-between gap-3 align-items-start mb-3">
                                        <div>
                                            <h5 class="card-title mb-1">Nạp trực tiếp tại trung tâm</h5>
                                            <p class="text-muted mb-0">Mang mã thanh toán này tới trung tâm hoặc điểm thu hộ để nhân viên xác nhận.</p>
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
                                                <div class="fw-semibold">{{ number_format($directRequest->amount, 0) }}₫</div>
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

                                    <h6>Hướng dẫn nộp tiền</h6>
                                    <ul class="mb-3">
                                        <li>Đến bất kỳ trung tâm thu hộ hoặc điểm thu tiền của Khai Trí.</li>
                                        <li>Thông báo bạn muốn nạp vào ví trực tiếp và cung cấp <strong>mã thanh toán</strong> ở trên.</li>
                                        <li>Thanh toán đúng số tiền trên mã để nhân viên xác nhận và cộng số dư cho bạn.</li>
                                    </ul>

                                    <div class="alert alert-warning mb-3">
                                        Yêu cầu này chỉ có thể được xác nhận bởi <strong>quản trị viên</strong> hoặc <strong>nhân viên</strong>. Vui lòng hoàn tất trước <strong>{{ $directRequest->expires_at_label }}</strong>.
                                    </div>

                                    <div class="d-flex flex-wrap gap-2">
                                        <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ $directRequest->reference }}').then(() => alert('Đã sao chép mã thanh toán'))">Sao chép mã</button>
                                        <a href="#wallet-topup-form" class="btn btn-outline-primary">Tạo yêu cầu mới</a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    @if(isset($qrToken) && $qrToken && in_array($paymentMethod, ['qr', 'bank'], true))
                        <div class="card mt-4 border-0 shadow-sm">
                            <div class="card-body text-center">
                                @if($paymentMethod === 'bank')
                                    <h5 class="card-title">Xác nhận chuyển khoản ngân hàng</h5>
                                    <p class="text-muted">Mã xác nhận của bạn: <strong>{{ $qrToken }}</strong></p>
                                @else
                                    <h5 class="card-title">Mã QR xác nhận giao dịch</h5>
                                    <p class="text-muted">Số tiền: <strong>{{ number_format($qrAmount ?? 0, 0) }}₫</strong></p>
                                @endif

                                @if($paymentMethod !== 'bank')
                                    <div class="mb-3">
                                        <img src="https://quickchart.io/qr?size=250&text={{ urlencode(json_encode(['token' => $qrToken, 'amount' => $qrAmount])) }}" alt="QR Code" class="img-fluid" />
                                    </div>
                                    <p class="text-muted small">Đây là mã QR nội bộ để nhận diện giao dịch, không phải mã QR của VNPay.</p>
                                @endif

                                <p class="text-muted">Sau khi chuyển xong, nhấn xác nhận để cập nhật số dư.</p>

                                <form action="{{ route('wallet.confirm-qr') }}" method="POST" class="d-flex justify-content-center gap-2">
                                    @csrf
                                    <input type="hidden" name="token" value="{{ $qrToken }}">
                                    <button type="submit" class="btn btn-success">Tôi đã chuyển xong</button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <p class="mt-4 text-muted mb-0">
                        Dữ liệu ví được đồng bộ với Hyperledger FireFly nếu môi trường đã cấu hình, đồng thời vẫn lưu trong hệ thống để thuận tiện tra cứu và quản lý.
                    </p>
                </div>
            </div>

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
                                            $method = data_get($tx->metadata, 'method', '-');
                                            $note = $tx->expiry_notice
                                                ?? data_get($tx->metadata, 'admin_note')
                                                ?? data_get($tx->metadata, 'failed_reason')
                                                ?? '—';
                                        @endphp
                                        <tr>
                                            <td>{{ $tx->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="text-capitalize">{{ $tx->type }}</td>
                                            <td>{{ strtoupper($method) }}</td>
                                            <td>{{ number_format($tx->amount, 0) }}₫</td>
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
                                            <td>
                                                <span class="badge {{ $tx->status_badge_class }}">{{ $tx->status_label }}</span>
                                            </td>
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
        const bankInstructions = document.getElementById('bank-instructions');
        const vnpayInstructions = document.getElementById('vnpay-instructions');
        const bankReference = document.getElementById('bank-reference');
        const amountInput = document.getElementById('amount');
        const submitButton = document.getElementById('wallet-submit-button');
        const qrToken = @json($qrToken ?? '');

        amountInput.addEventListener('blur', function () {
            const value = parseFloat(this.value);

            if (value < 1000 && value > 0) {
                this.value = 1000;
                alert('Số tiền tối thiểu là 1.000₫');
            } else if (value > 10000000) {
                this.value = 10000000;
                alert('Số tiền tối đa là 10.000.000₫');
            }
        });

        function refreshMethodUI() {
            const selected = document.querySelector('input[name="method"]:checked')?.value;

            if (bankInstructions) {
                bankInstructions.style.display = selected === 'bank' ? 'block' : 'none';
            }

            if (vnpayInstructions) {
                vnpayInstructions.style.display = selected === 'vnpay' ? 'block' : 'none';
            }

            if (bankReference) {
                bankReference.textContent = qrToken || '-';
            }

            if (submitButton) {
                submitButton.textContent = selected === 'vnpay'
                    ? 'Tiếp tục tới VNPay'
                    : 'Tạo yêu cầu nạp tiền';
            }
        }

        methodInputs.forEach((input) => input.addEventListener('change', refreshMethodUI));
        refreshMethodUI();
    });
</script>
@endpush
@endsection