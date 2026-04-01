<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mã OTP</title>
    <style>
        body {
            font-family: 'Inter', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3a8a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 30px;
        }
        .otp-code {
            background: #f8f9fa;
            border: 2px dashed #2c5aa0;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin: 20px 0;
            font-size: 32px;
            font-weight: bold;
            color: #2c5aa0;
            letter-spacing: 5px;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Khai Trí Edu</h1>
            <p>Hệ thống Giáo dục & Đào tạo</p>
        </div>

        <div class="content">
            <h2>{{ $purpose ?? 'Kích hoạt tài khoản' }}</h2>
            <p>Xin chào <strong>{{ $user->fullname }}</strong>,</p>

            <p>Cảm ơn bạn đã sử dụng <strong>Khai Trí Edu</strong>. Vui lòng dùng mã OTP dưới đây để tiếp tục:</p>

            <div class="otp-code">
                {{ $otp }}
            </div>

            <p>Mã OTP có hiệu lực trong <strong>10 phút</strong>. Nếu bạn không thực hiện yêu cầu này, vui lòng bỏ qua email.</p>

            <p>Trân trọng,<br><strong>Đội ngũ Khai Trí Edu</strong></p>
        </div>

        <div class="footer">
            <p>© {{ now()->year }} Khai Trí Edu. All rights reserved.</p>
            <p>Email: {{ config('mail.from.address', 'info@khaitri.edu.vn') }}</p>
        </div>
    </div>
</body>
</html>