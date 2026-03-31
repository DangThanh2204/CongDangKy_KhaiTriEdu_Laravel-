# Deploy Render Free

Project `khai-tri-edu` đã được chuẩn bị sẵn để demo trên Render bằng `Docker` và hỗ trợ 2 chế độ dữ liệu:

- `SQLite fallback` cho demo nhanh, ít lỗi
- `MySQL/MariaDB public` để gần giống môi trường local hơn

## Chế độ khuyến nghị nếu muốn giống local

Dùng `MySQL/MariaDB public` và để Render kết nối bằng các biến môi trường:

- `DB_CONNECTION=mysql`
- `DB_HOST=...`
- `DB_PORT=3306`
- `DB_DATABASE=...`
- `DB_USERNAME=...`
- `DB_PASSWORD=...`

## Import schema/data từ dump local

Repo hiện có file dump:

- `khaitriedu.sql`

File này có cả schema và dữ liệu, nên có thể dùng để khởi tạo public database gần giống local hơn.

### Muốn Render tự import dump khi DB đang trống

Thêm trên Render `Environment`:

- `DB_CONNECTION=mysql`
- `DB_HOST=...`
- `DB_PORT=3306`
- `DB_DATABASE=...`
- `DB_USERNAME=...`
- `DB_PASSWORD=...`
- `RENDER_IMPORT_SQL_DUMP=true`
- `RENDER_SQL_DUMP_PATH=/var/www/html/khaitriedu.sql`

Khi database chưa có bảng nào, start script sẽ tự import file dump này.

## Muốn luôn có dữ liệu mẫu/demo

Nếu ngoài dump local bạn vẫn muốn thêm bộ dữ liệu demo ổn định cho màn trình diễn, bật:

- `RENDER_SEED_DEMO=true`

Seeder `Database\Seeders\RenderDemoSeeder` sẽ bổ sung / cập nhật:

- admin demo
- giảng viên demo
- học viên demo
- khóa học demo online/offline
- module, nội dung học, đợt học
- đăng ký học mẫu
- thanh toán mẫu
- ví và nạp trực tiếp mẫu
- tin tức mẫu

## Trường hợp không có public MySQL ngay

Nếu chưa có MySQL/MariaDB public, bạn có thể để Render fallback về SQLite bằng:

- `RENDER_FALLBACK_SQLITE=true`

Khi không set `DB_CONNECTION`, start script sẽ tự dùng:

- `sqlite`
- file: `/tmp/render.sqlite`

## Các biến môi trường khác nên có nếu muốn bật đủ chức năng

### Social login

- `GOOGLE_CLIENT_ID`
- `GOOGLE_CLIENT_SECRET`
- `FACEBOOK_CLIENT_ID`
- `FACEBOOK_CLIENT_SECRET`

Tùy chọn:

- `GOOGLE_REDIRECT_URI`
- `FACEBOOK_REDIRECT_URI`

Nếu không nhập 2 biến redirect này, hệ thống sẽ tự fallback theo `APP_URL` hoặc `RENDER_EXTERNAL_URL`.

### Gemini assistant

- `GEMINI_API_KEY`

### Mail

- `MAIL_MAILER`
- `MAIL_SCHEME`
- `MAIL_HOST`
- `MAIL_PORT`
- `MAIL_USERNAME`
- `MAIL_PASSWORD`
- `MAIL_FROM_ADDRESS`
- `MAIL_FROM_NAME`

### VNPay

- `VNPAY_TMN_CODE`
- `VNPAY_HASH_SECRET`
- `VNPAY_RETURN_URL`
- `VNPAY_IPN_URL`

## Tài khoản demo của RenderDemoSeeder

- Admin: `admin@khaitri.edu.vn` / `Demo@123`
- Giảng viên: `giangvien@khaitri.edu.vn` / `Demo@123`
- Học viên 1: `hocvien1@khaitri.edu.vn` / `Demo@123`
- Học viên 2: `hocvien2@khaitri.edu.vn` / `Demo@123`
- Học viên 3: `hocvien3@khaitri.edu.vn` / `Demo@123`

## Lưu ý

- Render free vẫn sleep khi không có truy cập.
- Nếu dùng MySQL/MariaDB public, dữ liệu sẽ bền hơn SQLite fallback rất nhiều.
- Nếu `RENDER_IMPORT_SQL_DUMP=true`, dump chỉ tự import khi database đang trống.
- Nếu đã import dump local rồi mà vẫn bật `RENDER_SEED_DEMO=true`, seeder demo sẽ bổ sung / cập nhật thêm dữ liệu demo chứ không thay toàn bộ DB.