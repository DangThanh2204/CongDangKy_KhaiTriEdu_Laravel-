# Hướng Dẫn Cấu Hình Google và Facebook OAuth Login

Dự án đã được cập nhật để hỗ trợ đăng nhập bằng Google và Facebook. Dưới đây là hướng dẫn chi tiết để cấu hình các tính năng này.

## 1. Cài Đặt Và Migration

Trước tiên, hãy chạy migration để thêm các cột OAuth vào bảng users:

```bash
php artisan migrate
```

Các cột được thêm:
- `google_id` - ID Google
- `facebook_id` - ID Facebook  
- `provider` - Tên provider (google hoặc facebook)
- `provider_id` - ID của user từ provider
- `password` - Được đặt thành nullable để hỗ trợ OAuth users

## 2. Cấu Hình Google OAuth

### Bước 1: Tạo Google Cloud Project

1. Truy cập https://console.cloud.google.com/
2. Tạo một dự án mới (hoặc sử dụng dự án hiện có)
3. Đi tới "OAuth 2.0 Client IDs" trong "Credentials" section
4. Chọn "Create Credentials" > "OAuth client ID"
5. Chọn "Web application"

### Bước 2: Cấu Hình URI

Trong phần "Authorized redirect URIs", thêm:
```
http://localhost/auth/google/callback
https://yourdomain.com/auth/google/callback
```

### Bước 3: Lấy Client ID và Secret

Sao chép **Client ID** và **Client Secret** từ Google Console

### Bước 4: Thêm vào .env

```env
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

## 3. Cấu Hình Facebook OAuth

### Bước 1: Tạo Facebook App

1. Truy cập https://developers.facebook.com/
2. Đi tới "My Apps" và tạo một ứng dụng mới
3. Chọn "Integrate Facebook Login"
4. Chọn "Web" làm platform

### Bước 2: Cấu Hình OAuth Redirect URIs

Trong phần Settings > Basic, lấy **App ID** và **App Secret**

Trong phần Facebook Login > Settings, thêm các URI sau vào "Valid OAuth Redirect URIs":
```
http://localhost/auth/facebook/callback
https://yourdomain.com/auth/facebook/callback
```

### Bước 3: Thêm vào .env

```env
FACEBOOK_CLIENT_ID=your_facebook_app_id_here
FACEBOOK_CLIENT_SECRET=your_facebook_app_secret_here
FACEBOOK_REDIRECT_URI=http://localhost/auth/facebook/callback
```

## 4. Routes Được Thêm

| Route | Mô Tả |
|-------|-------|
| `GET /auth/google` | Redirect tới Google login |
| `GET /auth/google/callback` | Google OAuth callback |
| `GET /auth/facebook` | Redirect tới Facebook login |
| `GET /auth/facebook/callback` | Facebook OAuth callback |

## 5. Cách Thức Hoạt Động

### Khi User Đăng Nhập Bằng OAuth:

1. User nhấn nút "Google" hoặc "Facebook"
2. Được redirect tới trang đăng nhập của provider
3. Sau khi xác thực, được redirect về callback URL
4. Hệ thống sẽ:
   - Tìm user theo `google_id` hoặc `facebook_id`
   - Nếu tìm thấy, tự động đăng nhập
   - Nếu không tìm thấy nhưng email tồn tại, liên kết tài khoản hiện tại
   - Nếu email mới, tạo tài khoản mới tự động

### User mới được tạo sẽ có:
- `is_verified = true` (tự động xác thực)
- `role = 'student'` (mặc định)
- Avatar được tải từ OAuth provider
- Email và tên được lấy từ provider

## 6. Kiểm Tra Kết Nối

Sau khi cấu hình, bạn có thể kiểm tra bằng cách:

1. Truy cập trang login
2. Nhấp vào nút "Google" hoặc "Facebook"
3. Đăng nhập bằng tài khoản của bạn
4. Bạn sẽ được tự động đăng nhập vào ứng dụng

## 7. Troubleshooting

### Lỗi: "Redirect URI mismatch"
- Đảm bảo redirect URI trong .env khớp với cấu hình trong Google/Facebook Console
- Đảm bảo sử dụng http:// cho localhost và https:// cho production

### Lỗi: "Can't find provider"
- Kiểm tra xem các packages `socialiteproviders/google` và `socialiteproviders/facebook` đã được cài đặt
- Chạy `composer install` nếu cần

### Avatar không tải được
- Kiểm tra folder `storage/app/public/avatars` có quyền ghi
- Chạy `php artisan storage:link` nếu cần

## 8. Các Tính Năng Bổ Sung

Hệ thống OAuth cũng:
- **Tự động tải avatar** từ OAuth provider
- **Tự động xác thực tài khoản** (bypass OTP verification)
- **Hỗ trợ liên kết tài khoản** nếu email đã tồn tại
- **Tự động sinh tên đăng nhập** từ tên của user

## 9. Bảo Mật

- Luôn sử dụng HTTPS trong production
- Không bao giờ commit credentials vào Git
- Sử dụng environment variables cho tất cả credentials
- Kiểm tra `config/services.php` để xem cách secrets được quản lý

## 10. Testing

Để kiểm tra với email thực:
1. Sử dụng tài khoản Google/Facebook thực để test
2. Kiểm tra user được tạo trong database
3. Xác minh rằng avatar được tải đúng

---

**Chúc bạn thành công với việc cấu hình OAuth!** 🎉
