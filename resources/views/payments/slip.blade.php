@extends('layouts.app')

@section('title', 'Phiếu thanh toán')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4 p-lg-5">
                    <div class="text-center mb-4">
                        <span class="badge bg-light text-primary border mb-3">Phiếu thanh toán</span>
                        <h2 class="fw-bold mb-2">{{ $payment->reference }}</h2>
                        <p class="text-muted mb-0">Theo dõi trạng thái và tiếp tục thanh toán cho khóa học của bạn.</p>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @php
                        $badgeClass = match ($payment->status) {
                            'completed' => 'success',
                            'failed' => 'danger',
                            default => 'warning text-dark',
                        };
                        $course = $payment->courseClass?->course;
                    @endphp

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="small text-muted mb-1">Số tiền</div>
                                <div class="fs-4 fw-bold text-primary">{{ number_format($payment->amount) }} VND</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                <div class="small text-muted mb-1">Trạng thái</div>
                                <div><span class="badge bg-{{ $badgeClass }}">{{ ucfirst($payment->status) }}</span></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Phương thức</div>
                                <div class="fw-semibold">{{ $payment->method_label }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100">
                                <div class="small text-muted mb-1">Khóa học</div>
                                <div class="fw-semibold">{{ $course?->title ?? 'Đang cập nhật' }}</div>
                            </div>
                        </div>
                    </div>

                    @if($payment->isPending() && $payment->isVnpay())
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
                                <p class="mb-2">Giao dịch đã được tạo từ ví nội bộ của bạn.</p>
                                <p class="small text-muted mb-0">Nếu trạng thái chưa cập nhật, vui lòng liên hệ quản trị viên để được kiểm tra thêm.</p>
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