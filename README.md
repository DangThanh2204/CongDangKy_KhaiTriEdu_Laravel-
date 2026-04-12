# Khai Tri Edu

Khai Tri Edu là hệ thống cổng đăng ký khóa học trực tuyến phục vụ đề tài đồ án tốt nghiệp. Phiên bản hiện tại của project đang vận hành theo kiến trúc Laravel 12 + MongoDB, hỗ trợ triển khai trên Render bằng Docker và có thêm các module thanh toán, ví nội bộ, trợ lý AI, thông báo portal và tích hợp blockchain consortium qua Hyperledger FireFly.

Demo đang dùng:
- Public site: `https://khai-tri-edu.onrender.com`
- Render deploy: `render.yaml`
- Database mặc định hiện tại: `MongoDB`

## Trạng thái hệ thống hiện tại

- Framework backend: Laravel 12, PHP 8.2
- Database chính: MongoDB thông qua `mongodb/laravel-mongodb`
- Frontend build: Vite
- UI: Blade + Bootstrap 5 + Font Awesome
- Xác thực xã hội: Google, Facebook
- Thanh toán: VNPay + ví nội bộ
- AI assistant: Gemini
- Blockchain audit / token flow: Hyperledger FireFly
- PDF documents: `barryvdh/laravel-dompdf`

Lưu ý quan trọng:
- Hệ thống hiện tại dùng MongoDB làm nguồn dữ liệu chính.
- File `khaitriedu.sql` và các migration archive chỉ còn vai trò dữ liệu lịch sử / dữ liệu nguồn để import sang Mongo khi cần.
- Các lệnh bootstrap và import Mongo nằm trong `routes/console.php`.

## Chức năng chính

### Public portal
- Trang chủ giới thiệu khóa học, tin tức và form liên hệ
- Danh sách khóa học, lọc theo nhóm ngành và hình thức học
- Trang chi tiết khóa học, module, đợt học, lịch học
- Tin tức và danh mục tin tức
- Trang liên hệ, giới thiệu, đối tác
- Xác thực chứng chỉ

### Người dùng và xác thực
- Đăng ký tài khoản, đăng nhập, xác thực OTP
- Quên mật khẩu bằng OTP
- Đăng nhập bằng Google / Facebook
- Hồ sơ cá nhân, đổi mật khẩu
- Phân quyền theo vai trò: admin, staff, instructor, student

### Ghi danh, lớp học và học tập
- Ghi danh khóa học online / offline
- Giữ chỗ và xác nhận giữ chỗ cho một số flow đăng ký
- Theo dõi trạng thái hồ sơ đăng ký
- Dashboard học viên / giảng viên / staff / admin
- Theo dõi tiến độ học, tài liệu, quiz attempt, chứng chỉ

### Thanh toán và ví
- Thanh toán VNPay cho đăng ký khóa học
- Ví nội bộ để nạp tiền và thanh toán
- Đồng bộ số dư ví
- In phiếu đăng ký / biên nhận PDF

### Thông báo và trợ lý AI
- Portal notifications cho học viên
- Nhắc lịch / nhắc giữ chỗ theo scheduler
- Trợ lý AI dùng Gemini để trả lời theo ngữ cảnh hệ thống

### Quản trị
- Quản lý người dùng
- Quản lý nhóm ngành khóa học
- Quản lý khóa học, module, video, quiz
- Quản lý đợt học / lớp học
- Quản lý đăng ký, thanh toán, giao dịch ví
- Quản lý bài viết và danh mục tin tức
- Quản lý khuyến mãi, mã giảm giá
- Quản lý backup, system logs, settings
- Dashboard blockchain và đồng bộ dữ liệu FireFly

## Kiến trúc dữ liệu hiện tại

Collection Mongo chính của hệ thống gồm:
- `users`
- `settings`
- `course_categories`
- `courses`
- `course_modules`
- `course_materials`
- `course_videos`
- `course_enrollments`
- `class_schedules`
- `payments`
- `wallets`
- `wallet_transactions`
- `posts`
- `post_categories`
- `quizzes`
- `quiz_questions`
- `quiz_answers`
- `quiz_attempts`
- `notifications`
- `assistant_conversations`
- `assistant_messages`
- `course_certificates`

Danh sách bootstrap đầy đủ được định nghĩa trong [routes/console.php](/d:/xampp/htdocs/doan/khai-tri-edu/routes/console.php:24).

## Công nghệ sử dụng

| Thành phần | Công nghệ |
|---|---|
| Backend | Laravel 12 |
| Ngôn ngữ | PHP 8.2 |
| Database chính | MongoDB |
| Mongo driver | `mongodb/laravel-mongodb` |
| Frontend build | Vite 7 |
| UI | Blade, Bootstrap 5, Font Awesome |
| Social login | Laravel Socialite Providers |
| PDF | `barryvdh/laravel-dompdf` |
| AI | Gemini API |
| Thanh toán | VNPay |
| Blockchain | Hyperledger FireFly |
| Deploy | Render + Docker |

## Cấu hình môi trường

File mẫu: `.env.example`

Các biến tối thiểu để chạy local với MongoDB:

```dotenv
APP_NAME="Khai Tri Edu"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mongodb
DB_URI=
DB_DATABASE=khai_tri_edu

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
```

Các nhóm biến tùy chọn:
- Google OAuth: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`, `GOOGLE_REDIRECT_URI`
- Facebook OAuth: `FACEBOOK_CLIENT_ID`, `FACEBOOK_CLIENT_SECRET`, `FACEBOOK_REDIRECT_URI`
- Gemini: `GEMINI_API_KEY`, `GEMINI_BASE_URL`, `GEMINI_ASSISTANT_MODEL`
- VNPay: `VNPAY_URL`, `VNPAY_TMN_CODE`, `VNPAY_HASH_SECRET`, `VNPAY_RETURN_URL`, `VNPAY_IPN_URL`
- FireFly: `FIREFLY_URL`, `FIREFLY_API_KEY`, `FIREFLY_NAMESPACE`, `FIREFLY_TOKEN_POOL`, ...

## Chạy local

### 1. Clone project

```bash
git clone https://github.com/DangThanh2204/CongDangKy_KhaiTriEdu_Laravel-.git
cd khai-tri-edu
```

### 2. Cài dependencies

```bash
composer install
npm install
```

### 3. Tạo `.env`

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

### 4. Sinh key ứng dụng

```bash
php artisan key:generate
```

### 5. Cấu hình MongoDB

Điền một trong hai cách:

```dotenv
DB_CONNECTION=mongodb
DB_URI=mongodb+srv://username:password@cluster.mongodb.net/khai_tri_edu?retryWrites=true&w=majority
```

hoặc

```dotenv
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=khai_tri_edu
DB_USERNAME=
DB_PASSWORD=
```

### 6. Bootstrap Mongo collections

```bash
php artisan mongodb:bootstrap
```

Nếu muốn xóa dữ liệu cũ trước khi bootstrap:

```bash
php artisan mongodb:bootstrap --fresh
```

Nếu muốn nạp demo data:

```bash
php artisan mongodb:bootstrap --fresh --seed-demo
```

### 7. Import dữ liệu cũ từ SQL dump (tùy chọn)

Project hiện có hỗ trợ import dữ liệu lịch sử từ `khaitriedu.sql` sang MongoDB:

```bash
php artisan mongodb:import-sql-dump --dry-run
php artisan mongodb:import-sql-dump --fresh
```

### 8. Build frontend

```bash
npm run build
```

Hoặc chạy dev mode:

```bash
composer dev
```

### 9. Chạy ứng dụng

```bash
php artisan serve
```

## Lệnh hữu ích

```bash
composer test
php artisan optimize:clear
php artisan route:list
php artisan mongodb:bootstrap
php artisan mongodb:import-sql-dump --dry-run
php artisan mongodb:import-sql-dump --fresh
php artisan blockchain:sync-pending --limit=20
```

Scheduler quan trọng:
- `portal:dispatch-reminders`
- `blockchain:sync-pending --limit=20`

## Deploy Render

Project hiện đã chuẩn bị để deploy Render bằng Docker qua [render.yaml](/d:/xampp/htdocs/doan/khai-tri-edu/render.yaml:1).

### Env quan trọng trên Render

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://khai-tri-edu.onrender.com
APP_KEY=base64:...

DB_CONNECTION=mongodb
DB_DATABASE=khai_tri_edu
DB_URI=mongodb+srv://...

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public

RENDER_RESET_DATABASE=false
RENDER_SEED_DEMO=false
```

### Sau khi deploy

Mở Render Shell và chạy:

```bash
php artisan mongodb:bootstrap
```

Nếu cần import dữ liệu cũ:

```bash
php artisan mongodb:import-sql-dump --dry-run
php artisan mongodb:import-sql-dump --fresh
```

Tài liệu liên quan:
- `docs/render-deploy.md`
- `docs/firefly-consortium.md`
- `docs/firefly-fabric-local-setup.md`

## Route chính

### Public
- `/`
- `/courses`
- `/courses/{course}`
- `/lich-khai-giang`
- `/news`
- `/news/{slug}`
- `/contact`
- `/about`
- `/assistant/history`
- `/assistant/chat`

### Authenticated user
- `/profile`
- `/notifications`
- `/wallet`
- `/payments/{payment}`
- `/documents/enrollments/{enrollment}/registration-form`
- `/documents/payments/{payment}/receipt`

### Admin
- `/admin/dashboard`
- `/admin/users`
- `/admin/course-categories`
- `/admin/courses`
- `/admin/classes`
- `/admin/enrollments`
- `/admin/payments`
- `/admin/wallet-transactions`
- `/admin/news`
- `/admin/news-categories`
- `/admin/quizzes`
- `/admin/videos`
- `/admin/promotions`
- `/admin/settings`
- `/admin/system-logs`
- `/admin/backups`
- `/admin/blockchain`

## Cấu trúc chính của project

```text
app/
  Http/Controllers/
    Admin/
    Student/
    Instructor/
  Models/
  Services/
  Support/
  View/Composers/

config/
database/
docs/
public/
resources/
routes/
tests/
```

## Ghi chú vận hành

- Hệ thống hiện tối ưu theo MongoDB, không còn coi MySQL là database chạy chính.
- Một số màn admin/public đã được refactor để tránh `withCount()` kiểu SQL khi chạy Mongo.
- Nếu dùng Render `free`, request đầu tiên có thể chậm do cold start.
- Nếu MongoDB Atlas đặt khác region với Render thì thời gian phản hồi có thể tăng rõ rệt.
- Các env cũ kiểu SQLite / MySQL fallback trên Render không còn là flow chính nữa.

## Testing

```bash
vendor\bin\phpunit --testsuite Unit
composer test
```

## Tác giả

- Sinh viên thực hiện: DangThanh2204
- Email liên hệ: `dhthanh2004@gmail.com`
- Repository: `https://github.com/DangThanh2204/CongDangKy_KhaiTriEdu_Laravel-.git`

## License

Project sử dụng giấy phép MIT.
