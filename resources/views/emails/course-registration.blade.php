<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $statusText }} - {{ $courseTitle }}</title>
    <style>
        body { margin: 0; padding: 0; background: #f8fafc; font-family: Arial, sans-serif; color: #0f172a; }
        .wrapper { max-width: 680px; margin: 0 auto; padding: 24px 16px; }
        .card { background: #ffffff; border-radius: 24px; overflow: hidden; box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08); }
        .hero { padding: 28px 28px 18px; background: linear-gradient(135deg, #1d4ed8, #0f172a); color: #ffffff; }
        .hero small { display: inline-block; padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,0.14); text-transform: uppercase; letter-spacing: .06em; }
        .hero h1 { margin: 14px 0 8px; font-size: 28px; line-height: 1.25; }
        .hero p { margin: 0; line-height: 1.7; color: rgba(255,255,255,0.86); }
        .content { padding: 28px; }
        .meta { width: 100%; border-collapse: collapse; margin: 18px 0; }
        .meta td { padding: 10px 0; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
        .meta td:first-child { width: 34%; color: #64748b; font-weight: 700; }
        .notice { padding: 16px 18px; border-radius: 18px; margin: 18px 0; background: #eff6ff; color: #1d4ed8; }
        .wallet-box { padding: 16px 18px; border-radius: 18px; margin: 18px 0; background: #f8fafc; border: 1px solid #e2e8f0; }
        .actions { margin-top: 24px; }
        .button { display: inline-block; margin-right: 12px; margin-bottom: 12px; padding: 12px 18px; border-radius: 999px; text-decoration: none; font-weight: 700; }
        .button-primary { background: #1d4ed8; color: #ffffff; }
        .button-secondary { background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; }
        .footer { padding: 0 28px 28px; color: #64748b; font-size: 14px; line-height: 1.7; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="card">
            <div class="hero">
                <small>Khai Trí Edu</small>
                <h1>{{ $statusText }}</h1>
                <p>Xin chào {{ $userName }}, {{ $statusMessage }}</p>
            </div>
            <div class="content">
                <table class="meta" role="presentation">
                    <tr>
                        <td>Khóa học</td>
                        <td><strong>{{ $courseTitle }}</strong></td>
                    </tr>
                    <tr>
                        <td>Đợt học / lớp</td>
                        <td>{{ $className }}</td>
                    </tr>
                    <tr>
                        <td>Hình thức học</td>
                        <td>{{ $deliveryModeLabel }}</td>
                    </tr>
                    @if(!empty($categoryName))
                        <tr>
                            <td>Nhóm ngành</td>
                            <td>{{ $categoryName }}</td>
                        </tr>
                    @endif
                    @if(!empty($instructorName))
                        <tr>
                            <td>Giảng viên</td>
                            <td>{{ $instructorName }}</td>
                        </tr>
                    @endif
                    @if(!empty($startDateLabel))
                        <tr>
                            <td>Khai giảng</td>
                            <td>{{ $startDateLabel }}</td>
                        </tr>
                    @endif
                    @if(!empty($endDateLabel))
                        <tr>
                            <td>Kết thúc dự kiến</td>
                            <td>{{ $endDateLabel }}</td>
                        </tr>
                    @endif
                    @if(!empty($scheduleText))
                        <tr>
                            <td>Lịch học</td>
                            <td>{{ $scheduleText }}</td>
                        </tr>
                    @endif
                    @if(!empty($meetingInfo))
                        <tr>
                            <td>Ghi chú lớp học</td>
                            <td>{{ $meetingInfo }}</td>
                        </tr>
                    @endif
                </table>

                @if($walletPaid && $amount > 0)
                    <div class="wallet-box">
                        Hệ thống đã ghi nhận thanh toán từ ví nội bộ với số tiền <strong>{{ number_format($amount) }}đ</strong> cho lần đăng ký này.
                    </div>
                @endif

                <div class="notice">
                    {{ $statusMessage }}
                </div>

                <div class="actions">
                    <a href="{{ $dashboardUrl }}" class="button button-primary">Mở dashboard học viên</a>
                    <a href="{{ $courseUrl }}" class="button button-secondary">Xem lại khóa học</a>
                    @if(!empty($learnUrl))
                        <a href="{{ $learnUrl }}" class="button button-secondary">Vào học ngay</a>
                    @endif
                </div>
            </div>
            <div class="footer">
                Email này được gửi tự động từ hệ thống đăng ký khóa học trực tuyến Khai Trí. Nếu bạn cần hỗ trợ thêm về lịch khai giảng, duyệt đăng ký hoặc thanh toán, vui lòng phản hồi lại email này hoặc liên hệ trung tâm.
            </div>
        </div>
    </div>
</body>
</html>