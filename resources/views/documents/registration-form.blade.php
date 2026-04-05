<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Phiếu đăng ký khóa học</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #16213e; font-size: 13px; line-height: 1.55; margin: 0; }
        .page { padding: 28px 34px; }
        .header { border-bottom: 2px solid #2563eb; padding-bottom: 16px; margin-bottom: 22px; }
        .brand { font-size: 24px; font-weight: 700; color: #1d4ed8; margin-bottom: 4px; }
        .subtitle { color: #475569; font-size: 12px; }
        .title-row { margin: 20px 0 16px; }
        .title { font-size: 22px; font-weight: 700; margin: 0 0 4px; }
        .code { color: #475569; font-size: 12px; }
        .grid { width: 100%; border-collapse: separate; border-spacing: 0 10px; margin-bottom: 18px; }
        .grid td { vertical-align: top; width: 50%; padding-right: 12px; }
        .panel { border: 1px solid #dbe4f0; border-radius: 12px; padding: 14px 16px; background: #f8fbff; min-height: 92px; }
        .label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.06em; color: #64748b; margin-bottom: 6px; }
        .value { font-size: 15px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .muted { color: #475569; font-size: 12px; }
        .section-title { font-size: 14px; font-weight: 700; margin: 18px 0 10px; color: #0f172a; }
        table.detail { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.detail th, table.detail td { border: 1px solid #dbe4f0; padding: 10px 12px; text-align: left; }
        table.detail th { width: 28%; background: #eff6ff; color: #1e3a8a; font-size: 12px; }
        .summary { margin-top: 16px; border: 1px solid #dbe4f0; border-radius: 12px; padding: 14px 16px; background: #ffffff; }
        .footer { margin-top: 24px; padding-top: 12px; border-top: 1px dashed #cbd5e1; color: #475569; font-size: 11px; }
        .status { display: inline-block; padding: 4px 10px; border-radius: 999px; background: #dbeafe; color: #1d4ed8; font-size: 11px; font-weight: 700; }
    </style>
</head>
<body>
@php
    $enrollmentStatus = match ($enrollment->status) {
        'approved' => 'Đã duyệt',
        'completed' => 'Hoàn thành',
        'rejected' => 'Bị từ chối',
        'cancelled' => 'Đã hủy',
        default => 'Chờ duyệt',
    };

    if (method_exists($enrollment, 'hasActiveSeatHold') && $enrollment->hasActiveSeatHold()) {
        $enrollmentStatus = 'Đang giữ chỗ 24h';
    } elseif (method_exists($enrollment, 'isWaitlisted') && $enrollment->isWaitlisted()) {
        $enrollmentStatus = 'Trong hàng chờ';
    }

    $paymentStatus = 'Chưa phát sinh giao dịch';
    $paymentMethod = 'Chưa phát sinh';

    if ($payment) {
        $paymentStatus = match ($payment->status) {
            'completed' => 'Đã thanh toán',
            'failed' => 'Thanh toán thất bại',
            default => 'Chờ thanh toán',
        };

        $paymentMethod = match ($payment->method) {
            'wallet' => 'Ví học tập',
            'vnpay' => 'VNPay',
            'promotion' => 'Ưu đãi / miễn phí',
            'bank_transfer' => 'Chuyển khoản',
            'cash' => 'Tiền mặt',
            'counter' => 'Tại quầy',
            default => ucfirst(str_replace('_', ' ', (string) $payment->method)),
        };
    }
@endphp
<div class="page">
    <div class="header">
        <div class="brand">Khai Tri Edu</div>
        <div class="subtitle">Cổng đăng ký khóa học trực tuyến - Phiếu được sinh tự động từ hệ thống</div>
    </div>

    <div class="title-row">
        <h1 class="title">Phiếu đăng ký khóa học</h1>
        <div class="code">Mã hồ sơ: HS-{{ str_pad((string) $enrollment->id, 6, '0', STR_PAD_LEFT) }}</div>
    </div>

    <table class="grid">
        <tr>
            <td>
                <div class="panel">
                    <div class="label">Học viên</div>
                    <div class="value">{{ $student->fullname ?? $student->username ?? 'Chưa cập nhật' }}</div>
                    <div class="muted">{{ $student->email ?? 'Chưa có email' }}</div>
                    <div class="muted">Tên đăng nhập: {{ $student->username ?? 'Chưa cập nhật' }}</div>
                </div>
            </td>
            <td>
                <div class="panel">
                    <div class="label">Trạng thái hồ sơ</div>
                    <div class="value"><span class="status">{{ $enrollmentStatus }}</span></div>
                    <div class="muted">Ngày nộp hồ sơ: {{ optional($enrollment->created_at)->format('d/m/Y H:i') ?: 'Chưa ghi nhận' }}</div>
                    <div class="muted">Ngày duyệt: {{ optional($enrollment->approved_at)->format('d/m/Y H:i') ?: 'Đang chờ xử lý' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Thông tin khóa học</div>
    <table class="detail">
        <tr><th>Khóa học</th><td>{{ $course?->title ?? 'Khóa học đã ẩn hoặc chưa cập nhật' }}</td></tr>
        <tr><th>Nhóm ngành</th><td>{{ $course?->category?->name ?? 'Chưa phân nhóm' }}</td></tr>
        <tr><th>Hình thức học</th><td>{{ $course?->delivery_mode_label ?? 'Chưa cập nhật' }}</td></tr>
        <tr>
            <th>Đợt học / lớp học</th>
            <td>
                {{ $class?->name ?? 'Chưa xếp lớp' }}
                @if($class?->start_date)
                    - Khai giảng {{ $class->start_date->format('d/m/Y') }}
                @endif
                @if($class?->schedule_text)
                    - {{ $class->schedule_text }}
                @endif
            </td>
        </tr>
    </table>

    <div class="section-title">Thanh toán hồ sơ</div>
    <table class="detail">
        <tr><th>Giá gốc</th><td>{{ number_format((float) ($enrollment->base_price ?? 0), 0, ',', '.') }} VND</td></tr>
        <tr><th>Giảm giá</th><td>{{ number_format((float) ($enrollment->discount_amount ?? 0), 0, ',', '.') }} VND</td></tr>
        <tr><th>Thực thu</th><td><strong>{{ number_format((float) ($enrollment->final_price ?? 0), 0, ',', '.') }} VND</strong></td></tr>
        <tr><th>Trạng thái thanh toán</th><td>{{ $paymentStatus }}</td></tr>
        <tr><th>Phương thức</th><td>{{ $paymentMethod }}</td></tr>
        @if($payment?->reference)
            <tr><th>Mã giao dịch</th><td>{{ $payment->reference }}</td></tr>
        @endif
    </table>

    @if(filled($enrollment->notes))
        <div class="summary">
            <strong>Ghi chú hồ sơ:</strong> {{ $enrollment->notes }}
        </div>
    @endif

    <div class="footer">
        Phiếu đăng ký này có giá trị tra cứu nội bộ tại Khai Tri Edu. Mọi thay đổi về duyệt hồ sơ, xếp lớp hoặc thanh toán sẽ được cập nhật theo dữ liệu mới nhất trên hệ thống.
    </div>
</div>
</body>
</html>