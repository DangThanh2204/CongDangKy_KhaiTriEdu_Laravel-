# Deploy Render Free

Project này đã được chuẩn bị sẵn cho hướng demo trên Render free bằng `Docker + SQLite`.

## Cách dùng nhanh

1. Đẩy source code của thư mục `khai-tri-edu` lên GitHub.
2. Trên Render chọn `New +` -> `Blueprint` hoặc `Web Service`.
3. Nếu dùng Blueprint, Render sẽ đọc file `render.yaml` trong repo.
4. Sau deploy lần đầu, truy cập trang chủ để Render tự migrate và seed demo data.

## Dữ liệu demo mặc định

Seeder `Database\\Seeders\\RenderDemoSeeder` sẽ tạo:

- 1 admin
- 1 giảng viên
- 3 học viên
- 3 khóa học mẫu gồm online và offline
- module, nội dung học, đợt học
- đăng ký học mẫu
- thanh toán mẫu
- ví và yêu cầu nạp trực tiếp mẫu
- tin tức mẫu

### Tài khoản demo

- Admin: `admin@khaitri.edu.vn` / `Demo@123`
- Giảng viên: `giangvien@khaitri.edu.vn` / `Demo@123`
- Học viên 1: `hocvien1@khaitri.edu.vn` / `Demo@123`
- Học viên 2: `hocvien2@khaitri.edu.vn` / `Demo@123`
- Học viên 3: `hocvien3@khaitri.edu.vn` / `Demo@123`

## Lưu ý quan trọng

- Render free sẽ sleep khi không có truy cập.
- SQLite trên Render free là dữ liệu tạm thời. Khi redeploy hoặc restart, dữ liệu có thể reset.
- Start script sẽ tự chạy migrate baseline và seed demo data lại để link demo luôn có dữ liệu.

## VNPay trên Render

Sau khi Render cấp domain public, có thể cấu hình:

- `VNPAY_TMN_CODE`
- `VNPAY_HASH_SECRET`

Nếu chưa set `VNPAY_RETURN_URL` và `VNPAY_IPN_URL`, start script sẽ tự lấy `RENDER_EXTERNAL_URL` để gán:

- `/payments/vnpay/return`
- `/payments/vnpay/ipn`

Khi đăng ký merchant sandbox, dùng domain Render public của service.