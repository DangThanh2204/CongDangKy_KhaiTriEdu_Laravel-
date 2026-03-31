# Migration Baseline

Các migration lịch sử đã được archive vào:
- `database/migrations_archive/2026-03-28_mysql_baseline`

Schema hiện tại của project được baseline tại:
- `database/schema/mysql-schema.sql`

Từ sau mốc này:
- DB mới dùng `php artisan migrate` sẽ nạp schema baseline trước
- Các thay đổi schema mới tiếp tục tạo migration mới trong thư mục `database/migrations`

Lưu ý:
- Baseline này được tạo từ MySQL/XAMPP đang chạy thực tế của project
- Khi cần tra lịch sử chi tiết, xem thư mục archive ở trên