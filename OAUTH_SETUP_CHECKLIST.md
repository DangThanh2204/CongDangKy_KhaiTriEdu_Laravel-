# ✅ Setup Checklist - Google & Facebook OAuth Login

## Tổng Quan
Chức năng đăng nhập bằng Google và Facebook đã được thêm vào ứng dụng khai-tri-edu.

## 📋 Các Thay Đổi Được Thực Hiện

### 📁 Files Mới Tạo:
- ✅ `app/Http/Controllers/SocialAuthController.php` - Controller xử lý OAuth
- ✅ `database/migrations/2026_03_11_000000_add_oauth_to_users_table.php` - Migration thêm OAuth fields
- ✅ `OAUTH_SETUP.md` - Hướng dẫn chi tiết cấu hình

### 📝 Files Được Cập Nhật:
- ✅ `app/Models/User.php` - Thêm OAuth fields vào fillable
- ✅ `config/services.php` - Thêm cấu hình Google & Facebook
- ✅ `routes/web.php` - Thêm OAuth routes
- ✅ `resources/views/auth/login.blade.php` - Thêm social login buttons
- ✅ `resources/views/auth/register.blade.php` - Thêm social login buttons
- ✅ `.env.example` - Thêm OAuth environment variables

## 🚀 Các Bước Tiếp Theo

### 1️⃣ Chạy Migration
```bash
php artisan migrate
```

### 2️⃣ Cấu Hình Google OAuth
1. Truy cập: https://console.cloud.google.com/
2. Tạo OAuth 2.0 Client ID (Web application)
3. Thêm redirect URI: `http://localhost/auth/google/callback`
4. Lấy Client ID và Secret

### 3️⃣ Cấu Hình Facebook OAuth  
1. Truy cập: https://developers.facebook.com/
2. Tạo ứng dụng mới
3. Thêm Facebook Login product
4. Thêm redirect URI: `http://localhost/auth/facebook/callback`
5. Lấy App ID và App Secret

### 4️⃣ Cập Nhật .env
Thêm các biến sau vào file `.env` của bạn:

```env
# Google OAuth
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback

# Facebook OAuth
FACEBOOK_CLIENT_ID=your_app_id
FACEBOOK_CLIENT_SECRET=your_app_secret
FACEBOOK_REDIRECT_URI=http://localhost/auth/facebook/callback
```

### 5️⃣ Kiểm Tra Hoạt Động
1. Khởi động ứng dụng: `php artisan serve`
2. Truy cập trang login: `http://localhost:8000/login`
3. Nhấp nút "Google" hoặc "Facebook"
4. Kiểm tra xem bạn có thể đăng nhập thành công

## 🔐 Bảo Mật

⚠️ **Quan Trọng:**
- Không bao giờ commit credentials vào Git
- Luôn sử dụng environment variables
- Sử dụng HTTPS trong production
- Kiểm tra redirect URI khớp giữa .env và console

## 📚 Tài Liệu

Chi tiết đầy đủ: Xem file `OAUTH_SETUP.md`

## 🎨 Tính Năng

✨ **Những gì được hỗ trợ:**
- ✅ Đăng nhập bằng Google
- ✅ Đăng nhập bằng Facebook
- ✅ Tự động tạo tài khoản nếu email mới
- ✅ Liên kết tài khoản nếu email đã tồn tại
- ✅ Tự động tải avatar
- ✅ Tự động xác thực email
- ✅ Người dùng OAuth mặc định là student

## 🛠️ Troubleshooting

**Vấn đề:** Lỗi "Redirect URI mismatch"
- Đảm bảo redirect URI trong .env khớp với console

**Vấn đề:** Không tìm thấy provider
- Chạy: `composer update`

**Vấn đề:** Avatar không hiển thị
- Chạy: `php artisan storage:link`

## 📞 Hỗ Trợ

Nếu gặp vấn đề, kiểm tra:
1. File `.env` có đúng credentials không
2. Các routes có được đăng ký không: `php artisan route:list | grep auth`
3. Database migration chạy thành công: `php artisan migrate:status`

---

**Hoàn thành!** OAuth login đã sẵn sàng để cấu hình. 🎉
