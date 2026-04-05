<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Biên nhận thanh toán</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #16213e; font-size: 13px; line-height: 1.55; margin: 0; }
        .page { padding: 28px 34px; }
        .header { display: table; width: 100%; border-bottom: 2px solid #16a34a; padding-bottom: 16px; margin-bottom: 22px; }
        .header-left, .header-right { display: table-cell; vertical-align: top; }
        .header-right { text-align: right; }
        .brand { font-size: 24px; font-weight: 700; color: #166534; margin-bottom: 4px; }
        .subtitle { color: #475569; font-size: 12px; }
        .title { font-size: 22px; font-weight: 700; margin: 0 0 4px; }
        .code { color: #475569; font-size: 12px; }
        table.detail { width: 100%; border-collapse: collapse; margin-top: 14px; }
        table.detail th, table.detail td { border: 1px solid #dbe4f0; padding: 10px 12px; text-align: left; }
        table.detail th { width: 28%; background: #f0fdf4; color: #166534; font-size: 12px; }
        .totals { margin-top: 18px; width: 100%; border-collapse: collapse; }
        .totals td { padding: 8px 10px; border-bottom: 1px solid #e2e8f0; }
        .totals .label { color: #475569; }
        .totals .value { text-align: right; font-weight: 700; color: #0f172a; }
        .totals .highlight td { font-size: 15px; color: #166534; }
        .note { margin-top: 18px; padding: 14px 16px; border: 1px dashed #94a3b8; border-radius: 12px; background: #f8fafc; }
        .footer { margin-top: 26px; padding-top: 12px; border-top: 1px dashed #cbd5e1; color: #475569; font-size: 11px; }
        .status { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #dcfce7; color: #166534; font-size: 11px; font-weight: 700; }
    </style>
</head>
<body>
@php
    $methodLabel = match ($payment->method) {
        'wallet' => 'Ví học tập',
        'vnpay' => 'VNPay',
        'promotion' => 'Ưu đãi / miễn phí',
        'bank_transfer' => 'Chuyển khoản',
        'cash' => 'Tiền mặt',
        'counter' => 'Tại quầy',
        default => ucfirst(str_replace('_', ' ', (string) $payment->method)),
    };

    $statusLabel = match ($payment->status) {
        'completed' => 'Đã thanh toán',
        'failed' => 'Thanh toán thất bại',
        default => 'Chờ thanh toán',
    };
@endphp
<div class="page">
    <div class="header">
        <div class="header-left">
            <div class="brand">Khai Tri Edu</div>
            <div class="subtitle">Biên nhận nội bộ cho giao dịch thanh toán khóa học</div>
        </div>
        <div class="header-right">
            <div class="title">Biên nhận thanh toán</div>
            <div class="code">Mã biên nhận: {{ $payment->reference ?: ('PAY-' . $payment->id) }}</div>
        </div>
    </div>

    <table class="detail">
        <tr><th>Học viên</th><td>{{ $student->fullname ?? $student->username ?? 'Chưa cập nhật' }}</td></tr>
        <tr><th>Email</th><td>{{ $student->email ?? 'Chưa có email' }}</td></tr>
        <tr><th>Khóa học</th><td>{{ $course?->title ?? 'Khóa học đã ẩn hoặc chưa cập nhật' }}</td></tr>
        <tr>
            <th>Đợt học / lớp học</th>
            <td>
                {{ $class?->name ?? 'Chưa xếp lớp' }}
                @if($class?->start_date)
                    - Khai giảng {{ $class->start_date->format('d/m/Y') }}
                @endif
            </td>
        </tr>
        <tr><th>Phương thức</th><td>{{ $methodLabel }}</td></tr>
        <tr><th>Trạng thái</th><td><span class="status">{{ $statusLabel }}</span></td></tr>
        <tr><th>Thời điểm ghi nhận</th><td>{{ optional($payment->paid_at)->format('d/m/Y H:i') ?: optional($payment->created_at)->format('d/m/Y H:i') }}</td></tr>
        @if($enrollment)
            <tr><th>Mã hồ sơ liên quan</th><td>HS-{{ str_pad((string) $enrollment->id, 6, '0', STR_PAD_LEFT) }}</td></tr>
        @endif
    </table>

    <table class="totals">
        <tr>
            <td class="label">Giá gốc</td>
            <td class="value">{{ number_format((float) ($payment->base_amount ?? $payment->amount), 0, ',', '.') }} VND</td>
        </tr>
        <tr>
            <td class="label">Giảm giá</td>
            <td class="value">{{ number_format((float) ($payment->discount_amount ?? 0), 0, ',', '.') }} VND</td>
        </tr>
        <tr class="highlight">
            <td class="label"><strong>Thực thu</strong></td>
            <td class="value"><strong>{{ number_format((float) $payment->amount, 0, ',', '.') }} VND</strong></td>
        </tr>
    </table>

    @if(filled($payment->notes))
        <div class="note">
            <strong>Ghi chú giao dịch:</strong><br>
            {{ $payment->notes }}
        </div>
    @endif

    <div class="footer">
        Biên nhận này được sinh tự động từ hệ thống Khai Tri Edu để phục vụ đối soát và báo cáo. Vui lòng lưu lại mã giao dịch để tiện kiểm tra khi cần hỗ trợ.
    </div>
</div>
</body>
</html>