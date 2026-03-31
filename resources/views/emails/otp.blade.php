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
        .btn {
            display: inline-block;
            background: #2c5aa0;
            color: white;
            padding: 12px 30px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Khai Trí Edu</h1>
            <p>Hệ Thống Giáo Dục & Đào Tạo</p>
        </div>
        
        <div class="content">
            <h2>{{ $purpose ?? 'Kích Hoạt Tài Khoản' }}</h2>
            <p>Xin chào <strong>{{ $user->fullname }}</strong>,</p>
            
            <p>Cảm ơn bạn đã đăng ký tài khoản tại <strong>Khai Trí Edu</strong>. 
               Để hoàn tất đăng ký, vui lòng sử dụng mã OTP dưới đây:</p>
            
            <div class="otp-code">
                {{ $otp }}
            </div>
            
            <p>Mã OTP có hiệu lực trong <strong>10 phút</strong>. 
               Nếu bạn không thực hiện đăng ký này, vui lòng bỏ qua email.</p>
            
            <p>Trân trọng,<br>
               <strong>Đội ngũ Khai Trí Edu</strong></p>
        </div>
        
        <div class="footer">
            <p>© 2024 Hệ Thống Giáo Dục Khai Trí. All rights reserved.</p>
            <p>Địa chỉ: 123 Đường ABC, Quận 1, TP.HCM</p>
            <p>Email: info@khaitri.edu.vn | Điện thoại: (028) 1234 5678</p>
        </div>
    </div>
</body>
</html>