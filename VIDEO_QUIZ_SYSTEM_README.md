# Khai Trí Edu - Video Streaming & Quiz System

## Tổng quan

Hệ thống Video Streaming & Quiz System là một phần mở rộng của nền tảng giáo dục Khai Trí Edu, cung cấp khả năng streaming video HLS chất lượng cao và hệ thống kiểm tra trực tuyến toàn diện.

## Tính năng chính

### 🎥 Video Streaming (HLS)
- **Upload video**: Hỗ trợ nhiều định dạng video phổ biến
- **Xử lý tự động**: Chuyển đổi sang HLS với multiple bitrate
- **Thumbnail tự động**: Tạo thumbnail cho video
- **Player HTML5**: Sử dụng HLS.js cho tương thích đa trình duyệt
- **Tiến độ học tập**: Theo dõi video đã xem của học viên

### 📝 Quiz System
- **Đa dạng câu hỏi**: Multiple choice, True/False, Essay
- **Thời gian giới hạn**: Có thể đặt thời gian cho bài kiểm tra
- **Tự động chấm điểm**: Cho câu hỏi trắc nghiệm
- **Lưu tiến độ**: Tự động lưu câu trả lời
- **Xem kết quả**: Chi tiết kết quả và giải thích

## Cài đặt

### 1. Chạy Migration
```bash
php artisan migrate
```

### 2. Cài đặt Dependencies
```bash
composer install
npm install
```

### 3. Cấu hình FFMpeg (cho Video Processing)
Đảm bảo FFMpeg đã được cài đặt trên server:
```bash
# Ubuntu/Debian
sudo apt-get install ffmpeg

# CentOS/RHEL
sudo yum install ffmpeg

# macOS
brew install ffmpeg
```

### 4. Cấu hình Storage
```bash
php artisan storage:link
```

### 5. Chạy Queue Worker (cho video processing)
```bash
php artisan queue:work
```

## Sử dụng

### Admin Interface

#### Quản lý Quiz
1. Truy cập `/admin/quizzes`
2. Tạo quiz mới với thông tin cơ bản
3. Thêm câu hỏi cho quiz
4. Gán quiz cho khóa học

#### Quản lý Video
1. Truy cập `/admin/videos`
2. Upload video mới
3. Hệ thống sẽ tự động xử lý video
4. Theo dõi tiến độ xử lý

### Student Interface

#### Xem Video
1. Truy cập `/student/videos`
2. Chọn video để xem
3. Sử dụng player với controls đầy đủ

#### Làm Quiz
1. Truy cập `/student/quizzes`
2. Chọn quiz để làm
3. Làm bài và nộp
4. Xem kết quả chi tiết

## API Endpoints

### Quiz APIs
```
GET    /api/quizzes              - Lấy danh sách quiz
POST   /api/quizzes              - Tạo quiz mới
GET    /api/quizzes/{id}         - Chi tiết quiz
PUT    /api/quizzes/{id}         - Cập nhật quiz
DELETE /api/quizzes/{id}         - Xóa quiz
POST   /api/quizzes/{id}/attempt - Bắt đầu làm quiz
POST   /api/quizzes/save-answer  - Lưu câu trả lời
POST   /api/quizzes/{id}/complete - Nộp bài
```

### Video APIs
```
GET    /api/videos               - Lấy danh sách video
POST   /api/videos               - Upload video mới
GET    /api/videos/{id}          - Chi tiết video
DELETE /api/videos/{id}          - Xóa video
POST   /api/videos/{id}/watched  - Đánh dấu đã xem
```

## Database Schema

### Quizzes Table
```sql
- id: Primary key
- title: Tên quiz
- description: Mô tả
- course_id: ID khóa học
- time_limit: Thời gian giới hạn (phút)
- available_from: Thời gian bắt đầu
- available_until: Thời gian kết thúc
- created_at, updated_at
```

### Quiz Questions Table
```sql
- id: Primary key
- quiz_id: ID quiz
- question_text: Nội dung câu hỏi
- question_type: Loại câu hỏi (multiple_choice, true_false, essay)
- options: Các lựa chọn (JSON)
- correct_answer: Đáp án đúng
- explanation: Giải thích
- points: Điểm số
```

### Quiz Attempts Table
```sql
- id: Primary key
- quiz_id: ID quiz
- student_id: ID học viên
- started_at: Thời gian bắt đầu
- completed_at: Thời gian hoàn thành
- score: Điểm số
- total_questions: Tổng số câu
- time_taken: Thời gian làm bài
```

### Course Videos Table
```sql
- id: Primary key
- course_id: ID khóa học
- title: Tên video
- description: Mô tả
- original_filename: Tên file gốc
- file_path: Đường dẫn file
- hls_playlist_path: Đường dẫn HLS playlist
- thumbnail_path: Đường dẫn thumbnail
- duration: Thời lượng
- file_size: Kích thước file
- status: Trạng thái (uploaded, processing, processed, failed)
- processing_progress: Tiến độ xử lý
```

## Video Processing Workflow

1. **Upload**: Video được upload lên server
2. **Validation**: Kiểm tra định dạng và kích thước
3. **Queue Job**: Tạo job xử lý video
4. **FFMpeg Processing**:
   - Tạo multiple bitrate streams (360p, 480p, 720p, 1080p)
   - Tạo HLS segments và playlist
   - Tạo thumbnail từ frame đầu tiên
5. **Update Status**: Cập nhật trạng thái thành 'processed'

## Security Features

- **Authentication**: Chỉ user đã đăng nhập mới truy cập
- **Authorization**: Phân quyền admin/student
- **File Validation**: Kiểm tra loại file và kích thước
- **CSRF Protection**: Bảo vệ các form
- **XSS Prevention**: Sanitize input data

## Performance Optimization

- **Lazy Loading**: Load video theo demand
- **Caching**: Cache kết quả quiz và video metadata
- **CDN Ready**: Chuẩn bị cho CDN integration
- **Database Indexing**: Index các trường tìm kiếm thường xuyên

## Troubleshooting

### Video không phát được
1. Kiểm tra HLS playlist có tồn tại không
2. Kiểm tra CORS settings
3. Xem console log của trình duyệt

### Quiz không lưu được
1. Kiểm tra session timeout
2. Xem network requests trong DevTools
3. Kiểm tra database connections

### Video processing thất bại
1. Kiểm tra FFMpeg đã cài đặt chưa
2. Xem queue worker có chạy không
3. Kiểm tra disk space và permissions

## Contributing

1. Fork repository
2. Tạo feature branch
3. Commit changes
4. Push to branch
5. Tạo Pull Request

## License

This project is licensed under the MIT License.