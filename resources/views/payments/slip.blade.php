@extends('layouts.app')

@section('title', 'Phiếu thanh toán')

@section('content')
@php
    $badgeClass = match ($payment->status) {
        'completed' => 'success',
        'failed' => 'danger',
        default => 'warning text-dark',
    };

    $statusLabel = match ($payment->status) {
        'completed' => 'Đã thanh toán',
        'failed' => 'Thất bại',
        default => 'Chờ thanh toán',
    };

    $methodLabel = match ($payment->method) {
        'wallet' => 'Ví học tập',
        'promotion' => 'Ưu đãi / miễn phí',
        'vnpay' => 'VNPay',
        'bank_transfer' => 'Chuyển khoản',
        'cash' => 'Tiền mặt',
        'counter' => 'Tại quầy',
        default => ucfirst(str_replace('_', ' ', (string) $payment->method)),
    };

    $course = $payment->courseClass?->course;
    $isAdmin = optional(auth()->user())->isAdmin();
    $receiptReady = $payment->isCompleted() && in_array($payment->method, ['wallet', 'vnpay'], true);
@endphp
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <span class="badge bg-light text-primary border mb-3">Phiếu thanh toán</span>
                        <h2 class="fw-bold mb-2">{{ $payment->reference }}</h2>
                        <p class="text-muted mb-0">Theo dõi trạng thái giao dịch và tiếp tục hoàn tất thanh toán cho khóa học của bạn.</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="small text-muted mb-1">Số tiền</div>
                                <div class="fs-4 fw-bold text-primary">{{ number_format((float) $payment->amount, 0, ',', '.') }} VND</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="small text-muted mb-1">Trạng thái</div>
                                <div><span class="badge bg-{{ $badgeClass }}">{{ $statusLabel }}</span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Phương thức</div>
                                <div class="fw-semibold">{{ $methodLabel }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Khóa học</div>
                                <div class="fw-semibold">{{ $course?->title ?? 'Đang cập nhật' }}</div>
                            </div>
                        </div>
                    </div>

                    @if($payment->isVnpay() && $vnpaySummary)
                        <div class="border rounded-3 p-3 mb-4 bg-light-subtle">
                            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                                <strong>Thông tin kết nối VNPay</strong>
                                <span class="badge {{ $vnpaySummary['environment'] === 'sandbox' ? 'bg-warning text-dark' : 'bg-success' }}">
                                    {{ $vnpaySummary['environment_label'] }}
                                </span>
                            </div>
                            <div class="small text-muted mb-1">Gateway: <code>{{ $vnpaySummary['gateway_url'] ?: 'Chưa cấu hình' }}</code></div>
                            <div class="small text-muted mb-1">Return URL: <code>{{ $vnpaySummary['return_url'] }}</code></div>
                            <div class="small text-muted">IPN URL: <code>{{ $vnpaySummary['ipn_url'] }}</code></div>
                        </div>

                        @if(! $vnpaySummary['configured'])
                            <div class="alert alert-warning border mb-4">
                                <div class="fw-semibold mb-2">VNPay chưa sẵn sàng để thanh toán</div>
                                <ul class="mb-0 ps-3">
                                    @foreach($vnpaySummary['issues'] as $issue)
                                        <li>{{ $issue }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @elseif($isAdmin && $vnpaySummary['environment'] === 'sandbox')
                            <div class="alert alert-info border mb-4">
                                <div class="fw-semibold mb-1">Bạn đang ở môi trường Sandbox</div>
                                <div class="small mb-0">Khi chuyển sang thanh toán thật, hãy thay <code>VNPAY_URL</code>, <code>VNPAY_TMN_CODE</code> và <code>VNPAY_HASH_SECRET</code> production trên server.</div>
                            </div>
                        @endif
                    @endif

                    @if($payment->isPending() && $payment->isVnpay() && ($vnpaySummary['configured'] ?? false))
                        <div class="alert alert-info border mb-4">
                            <div class="fw-semibold mb-1">Sẵn sàng thanh toán qua VNPay</div>
                            <div class="small mb-0">Bạn sẽ được chuyển sang cổng thanh toán VNPay để hoàn tất giao dịch an toàn.</div>
                        </div>

                        <div class="d-grid gap-3">
                            <a href="{{ route('payments.vnpay.redirect', $payment) }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-credit-card me-2"></i>Thanh toán ngay qua VNPay
                            </a>
                            <div class="small text-muted text-center">Sau khi thanh toán thành công, hệ thống sẽ tự cập nhật trạng thái đơn hàng.</div>
                        </div>
                    @else
                        <div class="border rounded-3 p-4 bg-light-subtle">
                            <h5 class="fw-bold mb-3">Hướng dẫn thanh toán</h5>

                            @if($payment->method === 'bank_transfer')
                                <p class="mb-2">Vui lòng dùng <strong>mã thanh toán {{ $payment->reference }}</strong> khi chuyển khoản để trung tâm đối soát nhanh hơn.</p>
                                <p class="small text-muted mb-0">Sau khi chuyển khoản xong, admin sẽ xác nhận và cập nhật trạng thái trên hệ thống.</p>
                            @elseif(in_array($payment->method, ['cash', 'counter'], true))
                                <p class="mb-2">Vui lòng mang <strong>mã thanh toán {{ $payment->reference }}</strong> đến trung tâm hoặc quầy thu để nhân viên hỗ trợ.</p>
                                <p class="small text-muted mb-0">Sau khi thu ngân xác nhận, trạng thái thanh toán sẽ được cập nhật trên hệ thống.</p>
                            @elseif($payment->method === 'wallet')
                                <p class="mb-2">Giao dịch đã được tạo từ ví học tập của bạn.</p>
                                <p class="small text-muted mb-0">Nếu trạng thái chưa cập nhật, vui lòng liên hệ quản trị viên để được kiểm tra thêm.</p>
                            @elseif($payment->isVnpay() && ! ($vnpaySummary['configured'] ?? false))
                                <p class="mb-0">Cổng VNPay hiện chưa được cấu hình đầy đủ nên bạn chưa thể tiếp tục thanh toán. Vui lòng liên hệ quản trị viên.</p>
                            @else
                                <p class="mb-0">Phiếu thanh toán này đang được xử lý. Vui lòng theo dõi thêm trong ít phút tới.</p>
                            @endif
                        </div>
                    @endif

                    @if(filled($payment->notes))
                        <div class="mt-4 small text-muted">
                            <strong>Ghi chú:</strong> {{ $payment->notes }}
                        </div>
                    @endif

                    <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
                        @if($receiptReady)
                            <a href="{{ route('documents.payment-receipt', $payment) }}" class="btn btn-success">
                                <i class="fas fa-file-invoice-dollar me-2"></i>Tải biên nhận PDF
                            </a>
                        @endif
                        @if($course)
                            <a href="{{ route('courses.show', $course) }}" class="btn btn-outline-primary">
                                <i class="fas fa-arrow-left me-2"></i>Quay lại khóa học
                            </a>
                        @endif
                        <a href="{{ route('wallet.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-wallet me-2"></i>Xem ví học tập
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection