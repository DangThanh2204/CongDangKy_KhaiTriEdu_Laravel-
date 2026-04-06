# FireFly Consortium Deployment

Tài liệu này mô tả cách cấu hình mô hình blockchain consortium 2-3 thành viên cho đề tài `Cổng đăng ký khóa học trực tuyến Khai Trí`.

## Mô hình khuyến nghị

Hệ thống nên có ít nhất 3 thành viên:

1. `Khai Trí`: đơn vị phát hành chứng chỉ và ghi nhận giao dịch tuyển sinh.
2. `Đối tác đào tạo`: xác nhận việc hoàn thành khóa học hoặc trạng thái lớp.
3. `Đơn vị xác thực`: thành viên độc lập chỉ giữ vai trò kiểm chứng proof.

Với 3 thành viên, quorum nên đặt là `2` để một chứng chỉ hoặc giao dịch được xem là hợp lệ khi có ít nhất 2 proof.

## Biến môi trường chính

### Trường hợp một thành viên chính

```env
FIREFLY_URL=https://firefly-khaitri.example.com
FIREFLY_AUTH_MODE=basic
FIREFLY_USERNAME=firefly
FIREFLY_PASSWORD=secret
FIREFLY_NAMESPACE=default
FIREFLY_MEMBER_LABEL=Khai Tri Edu
FIREFLY_MEMBER_ROLE=issuer
FIREFLY_TOKEN_POOL=credit-pool
FIREFLY_PLATFORM_IDENTITY=platform
FIREFLY_AUDIT_TOPIC=audit
```

### Trường hợp consortium 3 thành viên

```env
FIREFLY_CONSORTIUM_QUORUM=2
FIREFLY_CONSORTIUM_MEMBERS=[
  {
    "key":"khai-tri",
    "label":"Khai Tri Edu",
    "role":"issuer",
    "url":"https://firefly-khaitri.example.com",
    "namespace":"default",
    "auth_mode":"basic",
    "username":"firefly",
    "password":"secret1",
    "audit_topic":"audit",
    "platform_identity":"platform",
    "token_pool":"credit-pool",
    "enabled":true
  },
  {
    "key":"doi-tac-dao-tao",
    "label":"Đối tác đào tạo",
    "role":"training_partner",
    "url":"https://firefly-partner.example.com",
    "namespace":"default",
    "auth_mode":"basic",
    "username":"firefly",
    "password":"secret2",
    "audit_topic":"audit",
    "platform_identity":"partner",
    "enabled":true
  },
  {
    "key":"don-vi-xac-thuc",
    "label":"Đơn vị xác thực",
    "role":"verifier",
    "url":"https://firefly-verifier.example.com",
    "namespace":"default",
    "auth_mode":"basic",
    "username":"firefly",
    "password":"secret3",
    "audit_topic":"audit",
    "platform_identity":"verifier",
    "enabled":true
  }
]
```

## Gợi ý triển khai thật trên VPS Ubuntu

Mỗi thành viên nên chạy trên một VPS riêng hoặc ít nhất là một hostname riêng:

- `firefly-khaitri.example.com`
- `firefly-partner.example.com`
- `firefly-verifier.example.com`

Trên mỗi VPS:

1. Cài Docker, Docker Compose và FireFly CLI.
2. Khởi tạo stack FireFly.
3. Bật HTTPS và xác thực `basic auth` hoặc `bearer token`.
4. Kiểm tra endpoint `/api/v1/status` phản hồi thành công.

## Tích hợp với Laravel

1. Điền `FIREFLY_CONSORTIUM_MEMBERS` vào Render hoặc server production.
2. Chạy scheduler để đồng bộ proof định kỳ:

```bash
php artisan schedule:run
php artisan blockchain:sync-pending --limit=20
```

3. Kiểm tra dashboard admin tại `/admin/blockchain`.

## Kết quả demo nên có

- Chứng chỉ có mã QR dẫn tới trang xác thực công khai.
- Trang xác thực hiển thị hash, proof ratio và trạng thái từng thành viên.
- Dashboard admin hiển thị số lượng thành viên online, quorum, chứng chỉ đạt quorum và giao dịch đạt quorum.
