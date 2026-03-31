# Hướng Dẫn Cấu Hình OAuth (Google & Facebook)

## ✅ TRẠNG THÁI HIỆN TẠI:
- **Google OAuth**: ✅ Đã cấu hình xong
- **Facebook OAuth**: ❌ Chưa cấu hình (cần tạo app Facebook)

## 1. Cấu Hình Google OAuth ✅ HOÀN THÀNH

Google OAuth đã được cấu hình với thông tin từ file credentials bạn cung cấp:
- Client ID: `1047829011745-tjos5ro6mg8oknu5bkb4l31hib3h7rge.apps.googleusercontent.com`
- Redirect URI: `http://localhost:8000/auth/google/callback`

---

## 2. Cấu Hình Facebook OAuth (CẦN LÀM)

### Bước 1: Tạo Google Cloud Project
1. Truy cập: https://console.cloud.google.com/
2. Tạo project mới hoặc chọn project có sẵn

### Bước 2: Enable Google+ API
1. Trong Google Cloud Console, tìm "APIs & Services" > "Library"
2. Tìm và enable "Google+ API" (hoặc "Google People API")

### Bước 3: Tạo OAuth 2.0 Client ID
1. Vào "APIs & Services" > "Credentials"
2. Click "Create Credentials" > "OAuth 2.0 Client IDs"
3. Chọn "Web application"
4. Điền thông tin:
   - Name: "Khai Tri Edu"
   - Authorized redirect URIs: `http://localhost/auth/google/callback`
5. Click "Create"
6. Sao chép **Client ID** và **Client Secret**

### Bước 4: Cập Nhật .env
```dotenv
GOOGLE_CLIENT_ID=your_google_client_id_here
GOOGLE_CLIENT_SECRET=your_google_client_secret_here
GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
```

---

## 2. Cấu Hình Facebook OAuth

### Bước 1: Tạo Facebook App
1. Truy cập: https://developers.facebook.com/
2. Click "My Apps" > "Create App"
3. Chọn "Consumer" (hoặc "Business" nếu có)
4. Điền thông tin app:
   - App name: "Khai Tri Edu"
   - App contact email: your_email@example.com

### Bước 2: Thêm Facebook Login
1. Trong app dashboard, click "Add Product" > "Facebook Login"
2. Chọn "Set up" > "Web"
3. Nhập site URL: `http://localhost`

### Bước 3: Cấu Hình Redirect URI
1. Trong Facebook Login settings, thêm:
   - Valid OAuth Redirect URIs: `http://localhost/auth/facebook/callback`

### Bước 4: Lấy App ID & App Secret
1. Trong Settings > Basic, sao chép:
   - App ID
   - App Secret

### Bước 5: Cập Nhật .env
```dotenv
FACEBOOK_CLIENT_ID=your_facebook_app_id_here
FACEBOOK_CLIENT_SECRET=your_facebook_app_secret_here
FACEBOOK_REDIRECT_URI=http://localhost/auth/facebook/callback
```

---

## 3. Kiểm Tra & Chạy Lại

Sau khi cập nhật .env:

```bash
# Clear config cache
php artisan config:clear

# Restart server nếu cần
php artisan serve
```

---

## 4. Lưu Ý Quan Trọng

### Google OAuth:
- Đảm bảo "Google+ API" đã được enable
- Redirect URI phải chính xác: `http://localhost/auth/google/callback`
- Nếu chạy trên domain khác, cập nhật redirect URI tương ứng

### Facebook OAuth:
- App phải ở chế độ "Development" hoặc "Live" (không phải "In Review")
- Redirect URI phải chính xác: `http://localhost/auth/facebook/callback`
- Đảm bảo đã thêm Facebook Login product

### Debug:
- Nếu vẫn lỗi, kiểm tra Laravel log: `storage/logs/laravel.log`
- Đảm bảo `.env` không có khoảng trắng thừa
- Restart web server sau khi thay đổi `.env`

---

## 5. Test OAuth

1. Truy cập: `http://localhost/login`
2. Click nút "Google" hoặc "Facebook"
3. Đăng nhập và cấp quyền
4. Sẽ redirect về trang home với thông báo thành công