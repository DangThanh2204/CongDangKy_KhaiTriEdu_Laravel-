@extends('layouts.app')

@section('title', 'Lịch sử thanh toán')
@section('page-class', 'page-student-payment-history')

@push('styles')
    @vite('resources/css/pages/student/payment-history.css')
@endpush

@section('content')
    <div class="container py-5 student-payment-page">
        <section class="payment-history-hero mb-4">
            <div class="payment-history-hero-copy">
                <span class="payment-history-eyebrow">Thanh toán & biên nhận</span>
                <h1>Lịch sử thanh toán của bạn</h1>
                <p>Theo dõi toàn bộ phiếu thanh toán, giao dịch thành công, giao dịch đang xử lý và tải biên nhận PDF cho mọi giao dịch đã hoàn tất.</p>
            </div>
            <div class="payment-history-hero-actions">
                <a href="{{ route('student.dashboard') }}" class="btn btn-outline-primary">
                    <i class="fas fa-gauge-high me-2"></i>Về dashboard
                </a>
                <a href="{{ route('student.application-status') }}" class="btn btn-outline-dark">
                    <i class="fas fa-file-waveform me-2"></i>Tra cứu hồ sơ
                </a>
            </div>
        </section>

        <section class="payment-history-summary-grid mb-4">
            <article class="payment-history-summary-card tone-primary">
                <span>Tổng giao dịch</span>
                <strong>{{ number_format($summary['total']) }}</strong>
                <small>Tất cả phiếu thanh toán đã tạo cho tài khoản của bạn.</small>
            </article>
            <article class="payment-history-summary-card tone-success">
                <span>Đã hoàn tất</span>
                <strong>{{ number_format($summary['completed']) }}</strong>
                <small>Các giao dịch đã ghi nhận thanh toán thành công và có thể tải biên nhận PDF.</small>
            </article>
            <article class="payment-history-summary-card tone-warning">
                <span>Đang xử lý</span>
                <strong>{{ number_format($summary['pending']) }}</strong>
                <small>Các giao dịch còn chờ xác nhận, chờ admin hoặc chờ bạn hoàn tất thanh toán.</small>
            </article>
            <article class="payment-history-summary-card tone-info">
                <span>Tổng đã thanh toán</span>
                <strong>{{ number_format((float) $summary['completed_amount'], 0, ',', '.') }}đ</strong>
                <small>Tổng giá trị giao dịch completed đã được ghi nhận trên hệ thống.</small>
            </article>
        </section>

        <section class="payment-history-shell">
            <div class="payment-history-shell-header">
                <div>
                    <h2>Danh sách giao dịch</h2>
                    <p>Môi giao dịch đều có thể xem lại chi tiết. Khi trạng thái là completed, bạn có thể xuất biên nhận PDF ngay tại đây.</p>
                </div>
                <span class="payment-history-chip">{{ number_format($summary['failed']) }} giao dịch thất bại / hủy</span>
            </div>

            @if($payments->count() > 0)
                <div class="table-responsive payment-history-table-wrap">
                    <table class="table align-middle payment-history-table mb-0">
                        <thead>
                            <tr>
                                <th>Mã phiếu</th>
                                <th>Khóa học / lớp</th>
                                <th>Số tiền</th>
                                <th>Phương thức</th>
                                <th>Trạng thái</th>
                                <th>Thời gian</th>
                                <th class="text-end">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payments as $payment)
                                @php
                                    $statusBadge = match ($payment->status) {
                                        'completed' => 'success',
                                        'failed' => 'danger',
                                        default => 'warning text-dark',
                                    };
                                    $course = $payment->courseClass?->course;
                                    $class = $payment->courseClass;
                                    $moment = $payment->paid_at ?: $payment->created_at;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="payment-history-reference">{{ $payment->reference ?: ('PAY-' . $payment->id) }}</div>
                                        <div class="payment-history-subtext">ID #{{ $payment->id }}</div>
                                    </td>
                                    <td>
                                        <div class="payment-history-course">{{ $course?->title ?? 'Khóa học đã ẩn hoặc chưa cập nhật' }}</div>
                                        <div class="payment-history-subtext">
                                            {{ $class?->name ?? 'Chưa gắn lớp' }}
                                            @if($course?->category?->name)
                                                - {{ $course->category->name }}
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <div class="payment-history-amount">{{ number_format((float) $payment->amount, 0, ',', '.') }}đ</div>
                                        @if((float) ($payment->discount_amount ?? 0) > 0)
                                            <div class="payment-history-subtext">Giảm {{ number_format((float) $payment->discount_amount, 0, ',', '.') }}đ</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="payment-history-method">{{ $payment->method_label }}</div>
                                        @if($payment->discountCode?->code)
                                            <div class="payment-history-subtext">Mã {{ $payment->discountCode->code }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $statusBadge }}">{{ $payment->status_label }}</span>
                                    </td>
                                    <td>
                                        <div class="payment-history-time">{{ optional($moment)->format('d/m/Y H:i') ?: 'Chưa ghi nhận' }}</div>
                                        <div class="payment-history-subtext">Tạo lúc {{ optional($payment->created_at)->format('d/m/Y H:i') ?: '--' }}</div>
                                    </td>
                                    <td class="text-end">
                                        <div class="payment-history-actions">
                                            <a href="{{ route('payments.show', $payment) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>Xem chi tiết
                                            </a>
                                            @if($payment->isCompleted())
                                                <a href="{{ route('documents.payment-receipt', $payment) }}" class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-file-invoice-dollar me-1"></i>Tải PDF
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($payments->hasPages())
                    <div class="payment-history-pagination">
                        {{ $payments->links() }}
                    </div>
                @endif
            @else
                <div class="payment-history-empty">
                    <div class="payment-history-empty-icon">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <h3>Bạn chưa có giao dịch thanh toán nào</h3>
                    <p>Sau khi đăng ký và tạo phiếu thanh toán cho khóa học, lịch sử giao dịch sẽ xuất hiện tại đây để bạn theo dõi và xuất biên nhận khi cần.</p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a href="{{ route('courses.index') }}" class="btn btn-primary">
                            <i class="fas fa-book-open me-2"></i>Khám phá khóa học
                        </a>
                        <a href="{{ route('courses.intakes') }}" class="btn btn-outline-primary">
                            <i class="fas fa-calendar-check me-2"></i>Xem lịch khai giảng
                        </a>
                    </div>
                </div>
            @endif
        </section>
    </div>
@endsection
