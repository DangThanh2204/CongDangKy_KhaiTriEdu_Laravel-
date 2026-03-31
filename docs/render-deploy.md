# Deploy Render Free

Project `khai-tri-edu` đã được chuẩn bị sẵn để demo trên Render free bằng `Docker + SQLite`.

## Cách dùng nhanh

1. Đẩy source code của thư mục `khai-tri-edu` lên GitHub.
2. Trên Render chọn `New +` -> `Blueprint`.
3. Chọn repo chứa project, Render sẽ đọc file `render.yaml`.
4. Deploy lần đầu để Render tạo web service.
5. Sau khi service lên, cấu hình các biến môi trường trong mục `Environment` nếu muốn bật đủ tính năng.

## Dữ liệu demo mặc định

Seeder `Database\Seeders\RenderDemoSeeder` sẽ tạo:

- 1 admin
- 1 giảng viên
- 3 học viên
- 3 khóa học mẫu gồm online và offline
- module, nội dung học, đợt học
- đăng ký học mẫu
- thanh toán mẫu
- ví và yêu cầu nạp trực tiếp mẫu
- tin tức mẫu

## Tài khoản demo

- Admin: `admin@khaitri.edu.vn` / `Demo@123`
- Giảng viên: `giangvien@khaitri.edu.vn` / `Demo@123`
- Học viên 1: `hocvien1@khaitri.edu.vn` / `Demo@123`
- Học viên 2: `hocvien2@khaitri.edu.vn` / `Demo@123`
- Học viên 3: `hocvien3@khaitri.edu.vn` / `Demo@123`

## Lưu ý quan trọng

- Render free sẽ sleep khi không có truy cập.
- SQLite trên Render free là dữ liệu tạm thời. Khi redeploy hoặc restart, dữ liệu có thể reset.
- Start script sẽ tự chạy migrate baseline và seed demo data để link demo luôn có dữ liệu.

## Biến môi trường nên cấu hình để đủ chức năng

### Bắt buộc cho social login

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `FACEBOOK_CLIENT_ID`
- `FACEBOOK_CLIENT_SECRET`

### Tùy chọn cho redirect URI social login

Nếu không nhập 2 biến này, hệ thống sẽ tự dùng `APP_URL` hoặc `RENDER_EXTERNAL_URL`:

- `GOOGLE_REDIRECT_URI`
- `FACEBOOK_REDIRECT_URI`

Ví dụ nếu domain Render là `https://khai-tri-edu.onrender.com` thì callback là:

- `https://khai-tri-edu.onrender.com/auth/google/callback`
- `https://khai-tri-edu.onrender.com/auth/facebook/callback`

### Bật trợ lý ảo Gemini

- `GEMINI_API_KEY`

Có thể giữ mặc định:

- `GEMINI_BASE_URL=https://generativelanguage.googleapis.com/v1beta`
- `GEMINI_ASSISTANT_MODEL=gemini-2.5-flash-lite`

### Bật gửi mail thật

- `MAIL_MAILER`
- `MAIL_SCHEME`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`

### Bật VNPay

- `VNPAY_TMN_CODE`
- `VNPAY_HASH_SECRET`

Nếu không nhập thì có thể để start script tự gán theo domain Render, hoặc nhập rõ:

- `VNPAY_RETURN_URL=https://khai-tri-edu.onrender.com/payments/vnpay/return`
- `VNPAY_IPN_URL=https://khai-tri-edu.onrender.com/payments/vnpay/ipn`

### Tính năng blockchain / FireFly

Nếu bạn muốn demo phần này thì thêm:

- `FIREFLY_URL`
- `FIREFLY_API_KEY`
- `FIREFLY_NAMESPACE`
- `FIREFLY_TOKEN_POOL`
- `FIREFLY_SIGNER`

## Sau khi cập nhật Environment

Sau mỗi lần sửa biến môi trường trên Render:

1. `Save changes`
2. `Manual Deploy` -> `Deploy latest commit`

## Thiết lập Developer Console cho OAuth

### Google

Authorized redirect URI:

- `https://khai-tri-edu.onrender.com/auth/google/callback`

### Facebook

Valid OAuth Redirect URI:

- `https://khai-tri-edu.onrender.com/auth/facebook/callback`

Khi domain Render thay đổi, nhớ cập nhật lại redirect URI tương ứng.