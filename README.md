# 🎓 Hệ Thống Giáo Dục Khai Trí

> Nền tảng học tập trực tuyến toàn diện với các khóa học chất lượng cao, quản lý người dùng linh hoạt và công cụ quản trị mạnh mẽ.

![Laravel](https://img.shields.io/badge/Laravel-v12.0-FF2D20?logo=laravel)
![PHP](https://img.shields.io/badge/PHP-v8.2-777BB4?logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql)
![Bootstrap](https://img.shields.io/badge/Bootstrap-v5.3-7952B3?logo=bootstrap)
![License](https://img.shields.io/badge/License-MIT-green)

---

## 📋 Mục Lục

- [Giới Thiệu](#giới-thiệu)
- [Tính Năng Chính](#tính-năng-chính)
- [Yêu Cầu Hệ Thống](#yêu-cầu-hệ-thống)
- [Cài Đặt & Cấu Hình](#cài-đặt--cấu-hình)
- [Hướng Dẫn Sử Dụng](#hướng-dẫn-sử-dụng)
- [Cấu Trúc Project](#cấu-trúc-project)
- [Các Vai Trò & Quyền Hạn](#các-vai-trò--quyền-hạn)
- [API & Endpoints](#api--endpoints)
- [Xử Lý Lỗi Thường Gặp](#xử-lý-lỗi-thường-gặp)
- [Đóng Góp & Hỗ Trợ](#đóng-góp--hỗ-trợ)

---

## 🚀 Giới Thiệu

**Khai Trí Edu** là một nền tảng học tập trực tuyến hiện đại được xây dựng bằng **Laravel 12** với giao diện thân thiện và tính năng quản lý toàn diện. Hệ thống được thiết kế để hỗ trợ:

- 📚 **Quản lý khóa học** với các danh mục, mức độ khó khác nhau
- 👥 **Quản lý người dùng** với 4 vai trò khác nhau (Admin, Staff, Instructor, Student)
- 📰 **Hệ thống tin tức** với các danh mục và bài viết chi tiết
- ✅ **Đăng ký khóa học** với hệ thống phê duyệt
- 🎨 **Giao diện responsive** tối ưu cho mọi thiết bị
- 📊 **Dashboard quản trị** với các thống kê chi tiết

---

## ✨ Tính Năng Chính

### 👨‍💼 Quản Lý Người Dùng
- ✅ Đăng ký, đăng nhập, xác thực OTP
- ✅ 4 vai trò người dùng: Admin, Staff, Instructor, Student
- ✅ Quản lý hồ sơ cá nhân và avatar
- ✅ Xác thực email và bảo mật tài khoản

### 📚 Quản Lý Khóa Học
- ✅ Tạo, chỉnh sửa, xóa khóa học
- ✅ Phân loại khóa học theo danh mục
- ✅ 3 mức độ khó: Beginner, Intermediate, Advanced
- ✅ Quản lý giá khóa học và giá khuyến mãi
- ✅ Upload hình ảnh banner và thumbnail
- ✅ Tính năng Featured & Popular courses

### 📋 Danh Mục & Phân Loại
- ✅ Quản lý danh mục khóa học với icon và màu sắc
- ✅ Danh mục tin tức tùy chỉnh
- ✅ Hỗ trợ danh mục phân cấp (parent-child)
- ✅ Sắp xếp thứ tự danh mục

### 📰 Hệ Thống Tin Tức
- ✅ Tạo, chỉnh sửa, xóa bài viết
- ✅ Phân loại bài viết theo danh mục
- ✅ Bài viết nổi bật (Featured)
- ✅ Tính năng lọc theo danh mục
- ✅ Xem lượt bài viết theo danh mục

### 💾 Đăng Ký Khóa Học
- ✅ Học viên gửi yêu cầu đăng ký
- ✅ Admin phê duyệt/từ chối yêu cầu
- ✅ Thêm học viên thủ công bằng email
- ✅ Theo dõi trạng thái đăng ký
- ✅ Ghi chú khi từ chối yêu cầu

### 📊 Dashboard & Thống Kê
- ✅ Tổng số người dùng, khóa học, tin tức
- ✅ Biểu đồ thống kê theo thời gian
- ✅ Danh sách người dùng gần đây
- ✅ Trạng thái hệ thống

---

## 🔧 Yêu Cầu Hệ Thống

### Cấu Hình Tối Thiểu
- **PHP**: >= 8.2
- **MySQL**: >= 5.7 hoặc **MariaDB** >= 10.2
- **Composer**: >= 2.0
- **Node.js**: >= 18 (để chạy Vite)
- **npm** hoặc **yarn**

### Phần Mềm Cần Thiết
- **XAMPP** hoặc **WAMP** hoặc **LAMP** (để chạy locally)
- **Git** (tuỳ chọn)
- **VS Code** hoặc editor khác

---

## 📦 Cài Đặt & Cấu Hình

### Bước 1: Clone hoặc Tải Project

```bash
# Nếu sử dụng Git
git clone https://github.com/your-repo/khai-tri-edu.git
cd khai-tri-edu

# Hoặc sao chép trực tiếp vào thư mục htdocs của XAMPP
# D:\xampp\htdocs\khai-tri-edu
```

### Bước 2: Cài Đặt Dependencies

```bash
# Cài đặt PHP dependencies
composer install

# Cài đặt Node.js dependencies
npm install
# hoặc
yarn install
```

### Bước 3: Cấu Hình Environment

```bash
# Tạo file .env từ .env.example
cp .env.example .env

# Hoặc trên Windows
copy .env.example .env
```

Mở file `.env` và cập nhật các thông tin:

```dotenv
APP_NAME="Khai Trí Edu"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost/khai-tri-edu

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=khai_tri_edu
DB_USERNAME=root
DB_PASSWORD=

# Mail Configuration (tuỳ chọn)
MAIL_MAILER=log
MAIL_HOST=localhost
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@khai-tri-edu.local"

# OAuth (Google / Facebook)
# Để bật tính năng đăng nhập bằng Google/Facebook, bạn cần tạo ứng dụng trên
# Google Cloud Console / Facebook Developers và nhập thông tin vào file .env.
#
# 1) Đăng ký OAuth redirect URI:
#    - Google:  http://localhost/auth/google/callback
#    - Facebook: http://localhost/auth/facebook/callback
#
# 2) Thêm vào .env (ví dụ):
# GOOGLE_CLIENT_ID=...
# GOOGLE_CLIENT_SECRET=...
# GOOGLE_REDIRECT_URI=http://localhost/auth/google/callback
#
# FACEBOOK_CLIENT_ID=...
# FACEBOOK_CLIENT_SECRET=...
# FACEBOOK_REDIRECT_URI=http://localhost/auth/facebook/callback
```

### Bước 4: Tạo Application Key

```bash
php artisan key:generate
```

### Bước 5: Tạo Database

**Cách 1: Sử dụng phpMyAdmin**

1. Mở **phpMyAdmin**: http://localhost/phpmyadmin
2. Click vào **New** ở bên trái
3. Nhập tên database: `khai_tri_edu`
4. Chọn **utf8mb4_unicode_ci** làm Collation
5. Click **Create**

**Cách 2: Sử dụng Command Line**

```bash
# Nếu bạn có MySQL client cài đặt
mysql -u root -p -e "CREATE DATABASE khai_tri_edu CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
```

### Bước 6: Chạy Database Migrations

```bash
# Chạy tất cả migrations
php artisan migrate

# Hoặc nếu database đã tồn tại, reset trước
php artisan migrate:fresh
```

### Bước 7: Seed Database Với Dữ Liệu Mẫu

```bash
# Chạy seeder (bao gồm users, courses, posts, v.v.)
php artisan db:seed
```

### Bước 8: Build Frontend Assets

```bash
# Development mode
npm run dev

# Production mode
npm run build
```

### Bước 9: Khởi Động Development Server

**Cách 1: Sử dụng PHP Built-in Server**

```bash
php artisan serve
```

Ứng dụng sẽ chạy tại: **http://localhost:8000**

**Cách 2: Sử dụng XAMPP Apache**

1. Đảm bảo Apache đang chạy trong XAMPP Control Panel
2. Truy cập: **http://localhost/khai-tri-edu**

---

## 📖 Hướng Dẫn Sử Dụng

### 🔐 Đăng Nhập Hệ Thống
Lưu ý: nếu không chạy php artisan migrate ( câu lệnh này tạo csdl)
bạn có thể import file sql (khaitriedu.sql) mình đã để sẵn thêm thẳng vào mysql để chạy
#### Tài Khoản Admin (Mặc Định)
```
Username: admin
Password: 123456 (hoặc được tạo bằng seeder)
```

#### Tài Khoản Khác
Các tài khoản khác được tạo tự động bởi seeder với mật khẩu mặc định: `123456`

### 🏠 Trang Chủ

Trang chủ hiển thị:
- 🎯 Hero banner với thông điệp chính
- 🌟 Khóa học nổi bật
- 📚 Danh sách khóa học phổ biến
- 📰 Tin tức mới nhất
- 📊 Thống kê hệ thống

**URL**: `/` hoặc `http://localhost:8000/`

### 👨‍🎓 Khu Vực Học Viên

#### Xem Danh Sách Khóa Học
- Truy cập: `/courses`
- Lọc theo danh mục, mức độ khó
- Tìm kiếm khóa học
- Xem chi tiết và đăng ký

#### Dashboard Học Viên
- Truy cập: `/student/dashboard` (khi đã đăng nhập)
- Xem các khóa học đã đăng ký
- Theo dõi tiến độ học tập

#### Đăng Ký Khóa Học
1. Chọn khóa học muốn đăng ký
2. Click nút "Đăng ký"
3. Gửi yêu cầu
4. Chờ phê duyệt từ quản trị viên

### 📰 Quản Lý Tin Tức

#### Xem Tin Tức (Public)
- Truy cập: `/news`
- Xem bài viết nổi bật
- Lọc theo danh mục
- Tìm kiếm bài viết

#### Quản Lý Tin Tức (Admin)
- Truy cập: `/admin/news`
- **Tạo**: `/admin/news/create`
- **Chỉnh sửa**: `/admin/news/{id}/edit`
- **Xóa**: Sử dụng nút Delete trong bảng
- **Duyệt**: Bật/tắt trạng thái Published

**Các trường quan trọng:**
- Tiêu đề
- Nội dung (hỗ trợ rich text)
- Hình ảnh đặc trưng
- Danh mục bài viết
- Trạng thái (Draft/Published)

### 📚 Quản Lý Khóa Học

#### Danh Mục Khóa Học
- **Xem**: `/admin/course-categories`
- **Tạo**: `/admin/course-categories/create`
- **Chỉnh sửa**: `/admin/course-categories/{id}/edit`

**Các danh mục mặc định:**
- 💻 Lập trình Web
- 📱 Lập trình Mobile
- 📊 Khoa học Dữ liệu
- 🎨 Thiết kế Đồ họa
- 📈 Digital Marketing
- 💼 Kinh doanh & Khởi nghiệp
- 🌎 Ngoại ngữ

#### Quản Lý Khóa Học
- **Xem**: `/admin/courses`
- **Tạo**: `/admin/courses/create`
- **Chỉnh sửa**: `/admin/courses/{id}/edit`

**Thông tin cần nhập:**
- Tiêu đề khóa học
- Mô tả ngắn (< 255 ký tự)
- Mô tả chi tiết (HTML hỗ trợ)
- Danh mục
- Mức độ khó (Beginner/Intermediate/Advanced)
- Giá khóa học
- Giá khuyến mãi (tuỳ chọn)
- Hình ảnh banner và thumbnail
- Thời lượng (phút)
- Số bài học
- Trạng thái (Draft/Published)

### 👥 Quản Lý Người Dùng

#### Danh Sách Người Dùng
- Truy cập: `/admin/users`
- Tìm kiếm theo tên hoặc email
- Lọc theo vai trò

#### Thêm Người Dùng
- Truy cập: `/admin/users/create`
- Nhập username, email, mật khẩu
- Chọn vai trò
- Upload avatar (tuỳ chọn)

#### Chỉnh Sửa Người Dùng
- Truy cập: `/admin/users/{id}/edit`
- Cập nhật thông tin
- Thay đổi vai trò
- Xác thực email

### ✅ Quản Lý Đăng Ký Khóa Học

#### Yêu Cầu Chờ Duyệt
- Truy cập: `/admin/enrollments/pending`
- Xem tất cả yêu cầu đăng ký chưa được duyệt
- **Duyệt**: Nhấn nút Approve
- **Từ chối**: Nhấn nút Reject và ghi chú lý do

#### Danh Sách Đăng Ký
- Truy cập: `/admin/enrollments`
- Xem tất cả đăng ký (tất cả trạng thái)
- Lọc theo trạng thái
- Xóa đăng ký

#### Thêm Học Viên Thủ Công
- Truy cập: `/admin/enrollments/manual-create`
- Nhập email hoặc chọn người dùng hiện có
- Chọn khóa học
- Hệ thống sẽ tạo tài khoản (nếu email mới)

---

## 📁 Cấu Trúc Project

```
khai-tri-edu/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AdminCourseController.php
│   │   │   │   ├── AdminCourseCategoryController.php
│   │   │   │   ├── AdminNewsCategoryController.php
│   │   │   │   ├── AdminNewsController.php
│   │   │   │   ├── AdminEnrollmentController.php
│   │   │   │   ├── AdminController.php (Dashboard)
│   │   │   │   └── UserController.php
│   │   │   ├── AuthController.php (Đăng ký/Đăng nhập)
│   │   │   ├── CourseController.php
│   │   │   ├── NewsController.php
│   │   │   └── EnrollmentController.php
│   │   ├── Middleware/
│   │   └── Requests/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Course.php
│   │   ├── CourseCategory.php
│   │   ├── CourseEnrollment.php
│   │   ├── Post.php
│   │   └── PostCategory.php
│   └── Providers/
│       └── AppServiceProvider.php
├── database/
│   ├── migrations/
│   │   ├── 2025_11_08_175856_create_users_table.php
│   │   ├── 2025_11_20_124941_create_course_categories_table.php
│   │   ├── 2025_11_20_124942_create_courses_table.php
│   │   └── 2025_11_20_163739_create_course_enrollments_table.php
│   └── seeders/
│       └── DatabaseSeeder.php (Dữ liệu mẫu)
├── resources/
│   ├── css/
│   │   ├── app.css (Giao diện chính)
│   │   └── admin.css (Giao diện admin)
│   ├── js/
│   │   ├── app.js
│   │   ├── bootstrap.js
│   │   └── custom.js (JavaScript tùy chỉnh)
│   └── views/
│       ├── layouts/
│       │   ├── app.blade.php (Layout chính)
│       │   └── admin.blade.php (Layout admin)
│       ├── admin/
│       │   ├── dashboard.blade.php
│       │   ├── courses/
│       │   ├── course-categories/
│       │   ├── news/
│       │   ├── news-categories/
│       │   ├── users/
│       │   └── enrollments/
│       ├── courses/
│       │   ├── index.blade.php
│       │   └── show.blade.php
│       ├── news/
│       │   ├── index.blade.php
│       │   ├── show.blade.php
│       │   └── category.blade.php
│       ├── student/
│       │   └── dashboard.blade.php
│       ├── auth/
│       │   ├── register.blade.php
│       │   ├── login.blade.php
│       │   └── verify.blade.php
│       └── home.blade.php
├── routes/
│   └── web.php (Định tuyến)
├── config/
│   ├── app.php
│   ├── database.php
│   ├── auth.php
│   └── ... (các config khác)
├── public/
│   └── (tài nguyên công khai, hình ảnh, CSS/JS đã build)
├── storage/
│   ├── app/
│   │   ├── public/ (Upload công khai)
│   │   └── private/ (Upload riêng tư)
│   └── logs/
├── vendor/ (Dependencies PHP - tạo bởi Composer)
├── node_modules/ (Dependencies Node.js - tạo bởi npm)
├── .env (Cấu hình môi trường)
├── .env.example (Mẫu .env)
├── composer.json (Dependencies PHP)
├── package.json (Dependencies Node.js)
├── vite.config.js (Cấu hình Vite)
├── phpunit.xml (Cấu hình testing)
└── README.md (Tài liệu này)
```

---

## 👤 Các Vai Trò & Quyền Hạn

### 🔴 Admin (Quản Trị Viên)
- ✅ Quyền truy cập toàn bộ hệ thống
- ✅ Quản lý tất cả người dùng
- ✅ Quản lý khóa học, danh mục
- ✅ Quản lý tin tức, danh mục tin tức
- ✅ Xem dashboard và thống kê
- ✅ Duyệt/từ chối đăng ký
- ✅ Thêm học viên thủ công

### 🟡 Staff (Nhân Viên)
- ✅ Quản lý tin tức
- ✅ Quản lý danh mục tin tức
- ✅ Xem danh sách người dùng (xem chỉ)
- ❌ Không thể quản lý khóa học
- ❌ Không thể quản lý người dùng

### 🔵 Instructor (Giảng Viên)
- ✅ Xem các khóa học của mình
- ✅ Xem danh sách học viên đăng ký
- ✅ Xem thống kê khóa học
- ❌ Không thể tạo khóa học mới
- ❌ Không thể quản lý người dùng

### 🟢 Student (Học Viên)
- ✅ Xem danh sách khóa học công khai
- ✅ Đăng ký khóa học
- ✅ Xem dashboard cá nhân
- ✅ Xem tin tức
- ❌ Không thể truy cập admin panel

---

## 🔌 API & Endpoints

### 🔑 Authentication Routes
```
POST   /login                          Đăng nhập
GET    /verify                         Xác thực OTP
POST   /verify                         Submit OTP
POST   /resend-otp                     Gửi lại OTP
POST   /register                       Đăng ký tài khoản mới
POST   /logout                         Đăng xuất
```

### 📚 Courses Routes (Public)
```
GET    /courses                        Danh sách khóa học
GET    /courses/{course}               Chi tiết khóa học
POST   /courses/{course}/enroll        Đăng ký khóa học
POST   /courses/{course}/unenroll      Hủy đăng ký
```

### 📰 News Routes (Public)
```
GET    /news                           Danh sách tin tức
GET    /news/{slug}                    Chi tiết bài viết
GET    /news/category/{slug}           Tin tức theo danh mục
```

### 👨‍💼 Admin Routes
```
GET    /admin                          Dashboard
GET    /admin/users                    Danh sách người dùng
POST   /admin/users                    Tạo người dùng
PUT    /admin/users/{user}             Cập nhật người dùng
DELETE /admin/users/{user}             Xóa người dùng

GET    /admin/courses                  Danh sách khóa học
POST   /admin/courses                  Tạo khóa học
PUT    /admin/courses/{course}         Cập nhật khóa học
DELETE /admin/courses/{course}         Xóa khóa học

GET    /admin/course-categories        Danh sách danh mục khóa học
POST   /admin/course-categories        Tạo danh mục
PUT    /admin/course-categories/{id}   Cập nhật danh mục
DELETE /admin/course-categories/{id}   Xóa danh mục

GET    /admin/news                     Danh sách tin tức
POST   /admin/news                     Tạo tin tức
PUT    /admin/news/{post}              Cập nhật tin tức
DELETE /admin/news/{post}              Xóa tin tức

GET    /admin/enrollments              Danh sách đăng ký
GET    /admin/enrollments/pending      Yêu cầu chờ duyệt
POST   /admin/enrollments/{id}/approve Duyệt đăng ký
POST   /admin/enrollments/{id}/reject  Từ chối đăng ký
```

---

## 🐛 Xử Lý Lỗi Thường Gặp

### 1. Lỗi: "SQLSTATE[HY000] [2002] No such file or directory"

**Nguyên nhân**: Không thể kết nối MySQL

**Giải pháp**:
```bash
# Kiểm tra MySQL đang chạy (XAMPP)
# Khởi động MySQL từ XAMPP Control Panel

# Hoặc cấu hình MySQL server address trong .env
DB_HOST=127.0.0.1
DB_PORT=3306
```

### 2. Lỗi: "No application encryption key has been specified"

**Nguyên nhân**: Chưa tạo APP_KEY

**Giải pháp**:
```bash
php artisan key:generate
```

### 3. Lỗi: "The migration has already been published"

**Nguyên nhân**: Migration đã tồn tại

**Giải pháp**:
```bash
# Reset database hoặc xóa migration thừa
php artisan migrate:fresh
```

### 4. Lỗi: "Class not found" hay "Model not found"

**Nguyên nhân**: Chưa chạy autoload

**Giải pháp**:
```bash
composer dumpautoload
php artisan cache:clear
```

### 5. Lỗi: "419 Page Expired" (CSRF Token)

**Nguyên nhân**: CSRF token hết hạn hoặc không khớp

**Giải pháp**:
```bash
# Refresh trang và submit form lại
# Hoặc clear cache
php artisan cache:clear
```

### 6. Lỗi: "Vite manifest not found"

**Nguyên nhân**: Assets chưa được build

**Giải pháp**:
```bash
npm run dev
# hoặc
npm run build
```

### 7. Lỗi: "Permission denied" khi upload

**Nguyên nhân**: Folder storage không có quyền ghi

**Giải pháp**:
```bash
# Windows (bật Admin)
icacls "D:\xampp\htdocs\khai-tri-edu\storage" /grant:r "%USERNAME%":F /T

# Linux/Mac
chmod -R 777 storage bootstrap/cache
```

### 8. Lỗi: "Undefined variable" trong views

**Nguyên nhân**: Controller không pass dữ liệu đến view

**Giải pháp**:
```php
// Trong controller
return view('view-name', compact('variable'));
```

---

## 🌐 Demo Online

Để xem demo online, truy cập:
- 🏠 **Trang chủ**: http://localhost:8000 (hoặc http://localhost/khai-tri-edu)
- 📚 **Khóa học**: http://localhost:8000/courses
- 📰 **Tin tức**: http://localhost:8000/news
- 🔐 **Admin**: http://localhost:8000/admin

---

## 📊 Database Schema

### Bảng Chính

#### `users`
- `id` - ID người dùng
- `username` - Tên đăng nhập (unique)
- `fullname` - Họ tên
- `email` - Email (unique)
- `password` - Mật khẩu (hashed)
- `avatar` - Đường dẫn avatar
- `role` - Vai trò (admin, staff, instructor, student)
- `is_verified` - Trạng thái xác thực
- `otp` - Mã OTP
- `remember_token` - Token "Remember me"
- `timestamps` - Created_at, updated_at

#### `courses`
- `id` - ID khóa học
- `title` - Tiêu đề
- `slug` - URL slug (unique)
- `description` - Mô tả chi tiết
- `short_description` - Mô tả ngắn
- `price` - Giá gốc
- `sale_price` - Giá khuyến mãi
- `thumbnail` - Hình thumbnail
- `banner_image` - Hình banner
- `level` - Mức độ (beginner, intermediate, advanced)
- `duration` - Thời lượng (phút)
- `lessons_count` - Số bài học
- `students_count` - Số học viên
- `instructor_id` - ID giảng viên
- `category_id` - ID danh mục
- `status` - Trạng thái (draft, published)
- `is_featured` - Khóa học nổi bật
- `timestamps` - Created_at, updated_at

#### `course_categories`
- `id` - ID danh mục
- `name` - Tên danh mục
- `slug` - URL slug (unique)
- `description` - Mô tả
- `parent_id` - ID danh mục cha (phân cấp)
- `icon` - Icon emoji/Font Awesome
- `color` - Màu sắc (HEX)
- `order` - Thứ tự sắp xếp
- `is_active` - Trạng thái kích hoạt
- `timestamps` - Created_at, updated_at

#### `course_enrollments`
- `id` - ID đăng ký
- `user_id` - ID người dùng
- `course_id` - ID khóa học
- `status` - Trạng thái (pending, approved, rejected, completed)
- `requires_approval` - Cần phê duyệt
- `enrolled_at` - Ngày đăng ký
- `completed_at` - Ngày hoàn thành
- `approved_at` - Ngày phê duyệt
- `rejected_at` - Ngày từ chối
- `notes` - Ghi chú
- `unique` - (user_id, course_id)
- `timestamps` - Created_at, updated_at

#### `posts`
- `id` - ID bài viết
- `title` - Tiêu đề
- `slug` - URL slug (unique)
- `excerpt` - Tóm tắt
- `content` - Nội dung
- `featured_image` - Hình đặc trưng
- `category_id` - ID danh mục
- `author_id` - ID tác giả
- `is_featured` - Bài viết nổi bật
- `status` - Trạng thái (draft, published)
- `view_count` - Lượt xem
- `published_at` - Ngày xuất bản
- `timestamps` - Created_at, updated_at

#### `post_categories`
- `id` - ID danh mục
- `name` - Tên danh mục
- `slug` - URL slug (unique)
- `description` - Mô tả
- `color` - Màu sắc
- `order` - Thứ tự
- `is_active` - Trạng thái kích hoạt
- `timestamps` - Created_at, updated_at

---

## 🚀 Triển Khai (Deployment)

### Chuẩn Bị
1. Purchase hosting (nếu chưa có)
2. Tạo database từ cPanel/Plesk
3. Upload code lên server

### Quy Trình Deployment
```bash
# SSH vào server
ssh user@server.com

# Clone hoặc upload project
git clone https://your-repo.git
cd khai-tri-edu

# Cài đặt dependencies
composer install --optimize-autoloader --no-dev
npm install && npm run build

# Setup environment
cp .env.example .env
php artisan key:generate

# Cấu hình database trong .env
# (sửa DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# Chạy migrations
php artisan migrate --force

# Seed database (tuỳ chọn)
php artisan db:seed --force

# Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📝 Công Nghệ Sử Dụng

| Công Nghệ | Phiên Bản | Mục Đích |
|-----------|----------|---------|
| Laravel | 12.0 | Web Framework |
| PHP | 8.2+ | Ngôn ngữ lập trình |
| MySQL | 5.7+ | Database |
| Bootstrap | 5.3 | CSS Framework |
| Vite | 7.0 | Build tool |
| Axios | 1.11 | HTTP Client |
| Font Awesome | 6.4 | Icons |

---

## 📞 Đóng Góp & Hỗ Trợ

### Báo Cáo Lỗi

Nếu bạn phát hiện lỗi, vui lòng:
1. Kiểm tra lại các bước cài đặt
2. Xem mục "Xử Lý Lỗi Thường Gặp"
3. Liên hệ quản trị viên với mô tả chi tiết lỗi

### Đóng Góp Mã Nguồn

```bash
# Fork repository
# Clone từ fork của bạn
git clone https://github.com/your-username/khai-tri-edu.git

# Tạo branch mới
git checkout -b feature/your-feature-name

# Commit changes
git commit -m "Add your feature description"

# Push to branch
git push origin feature/your-feature-name

# Open Pull Request
```

### Liên Hệ

- 📧 **Email**: dhthanh2004@gmail.com
- 💬 **Chat**: Liên hệ qua admin panel
- 🐙 **GitHub**: [GitHub Repository URL]

---

## 📄 License

Project này được cấp phép dưới [MIT License](LICENSE).

---

## ✨ Changelog

### v1.0.0 (2025-11-28)
- ✅ Hệ thống xác thực (Register, Login, OTP Verification)
- ✅ Quản lý khóa học
- ✅ Quản lý danh mục khóa học
- ✅ Hệ thống đăng ký khóa học
- ✅ Quản lý tin tức
- ✅ Quản lý danh mục tin tức
- ✅ Quản lý người dùng
- ✅ Dashboard quản trị
- ✅ Giao diện responsive

---

## 🙏 Cảm Ơn

Cảm ơn đã sử dụng **Khai Trí Edu**. Nếu bạn thấy project hữu ích, vui lòng give it a ⭐ on GitHub!

---

**Happy Coding! 🚀**

*Last Updated: November 28, 2025*

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
