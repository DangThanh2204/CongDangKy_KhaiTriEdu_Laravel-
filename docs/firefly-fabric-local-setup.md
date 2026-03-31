# FireFly + Fabric Local Setup Cho Khai Tri Edu

## Mục tiêu

Tài liệu này giúp bạn dựng local stack `Hyperledger FireFly + Hyperledger Fabric` để nối với project Laravel `khai-tri-edu` theo hướng:

`Laravel -> FireFly -> Fabric`

## Tình trạng máy hiện tại

Khi kiểm tra trên máy này:

- `winget` có sẵn
- `docker` chưa cài
- `WSL2` chưa cài

Vì vậy hiện chưa thể kéo local FireFly/Fabric stack lên ngay trên máy này cho tới khi cài xong Docker + WSL2.

## Prerequisites theo docs chính thức FireFly

FireFly CLI yêu cầu:

- Docker
- Docker Compose
- OpenSSL
- Với Windows: FireFly khuyên dùng `WSL2`

## Bước 1: Cài WSL2

Mở PowerShell dưới quyền Administrator và chạy script hỗ trợ của project:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\install-wsl-ubuntu-docker.ps1
```

Script này sẽ:

- bật `WSL` và `VirtualMachinePlatform`
- nhắc restart nếu Windows yêu cầu
- cài `Ubuntu`
- thử cài `Docker Desktop`

Nếu bạn muốn làm tay, vẫn có thể chạy lệnh tối thiểu:

```powershell
wsl --install
```

Sau đó restart máy.

## Bước 2: Cài Docker Desktop

Mở PowerShell dưới quyền Administrator và chạy:

```powershell
winget install -e --id Docker.DockerDesktop
```

Sau khi cài:

1. Mở Docker Desktop
2. Bật chế độ dùng `WSL2`
3. Chờ Docker báo `Engine running`

## Bước 3: Cài FireFly CLI

Theo docs chính thức, cách dễ nhất là tải binary release mới nhất của FireFly CLI.

- Docs CLI: https://hyperledger.github.io/firefly/latest/gettingstarted/firefly_cli/
- Release page: https://github.com/hyperledger/firefly-cli/releases

Khi cài xong, kiểm tra:

```bash
ff version
```

## Bước 4: Tạo local Fabric stack

Với project Laravel này, bạn có 2 lựa chọn:

- Nhẹ hơn để nối app nhanh: `1 member`
- Đúng kiểu multiparty demo: `3 members`

Nếu máy không mạnh, nên bắt đầu bằng `1 member` trước.

Tạo stack Fabric:

```bash
ff init fabric
```

Ví dụ chọn:

- stack name: `khai-tri-fabric`
- member count: `1` hoặc `3`

Sau đó start stack:

```bash
ff start khai-tri-fabric
```

Khi chạy xong, FireFly thường in ra URL kiểu:

- API/UI member 0: `http://127.0.0.1:5000/ui`
- Sandbox member 0: `http://127.0.0.1:5109`

Với Laravel app này, `FIREFLY_URL` nên trỏ vào base API của member đầu tiên, thường là:

```text
http://127.0.0.1:5000
```

## Bước 5: Patch .env cho Laravel

Project đã có sẵn script hỗ trợ. Từ root project chạy:

```powershell
powershell -ExecutionPolicy Bypass -File .\scripts\configure-firefly-local.ps1 -EnvPath .\.env -FireflyUrl http://127.0.0.1:5000 -Namespace default
```

Script này sẽ cập nhật các biến:

- `FIREFLY_URL`
- `FIREFLY_API_KEY`
- `FIREFLY_NAMESPACE`
- `FIREFLY_TOKEN_POOL`
- `FIREFLY_TOKEN_NAME`
- `FIREFLY_PLATFORM_IDENTITY`
- `FIREFLY_SIGNER`
- `FIREFLY_AUDIT_TOPIC`

## Bước 6: Tạo token pool

Project hiện dùng `FIREFLY_TOKEN_POOL` cho các lệnh `mint` và `transfer`.

Bạn cần tạo 1 fungible token pool trong FireFly rồi điền `pool locator` hoặc id tương ứng vào `FIREFLY_TOKEN_POOL`.

Docs token: https://hyperledger.github.io/firefly/latest/tutorials/tokens/

## Bước 7: Lưu ý rất quan trọng về identity

Hiện tại project đang tạo `wallet.firefly_identity` theo dạng:

```text
user:<id>
```

Đây là placeholder tốt cho app-level mapping, nhưng để token transfer chạy thật trên FireFly/Fabric thì `from` / `to` phải là identity hoặc signer mà connector hiểu được.

Điều đó nghĩa là nếu bạn muốn đi tới mức enterprise thật:

1. Bạn cần có chiến lược map user Laravel sang FireFly/Fabric identity thật
2. Có thể cần cấp signer / account / verifier tương ứng cho từng user
3. Sau đó cập nhật `wallet.firefly_identity` sang giá trị được FireFly token connector chấp nhận

## Gợi ý rollout thực tế

### Giai đoạn 1: Demo thật nhưng gọn

- Dùng `1 member`
- Dùng `platform` identity để test token pool
- Chạy thành công các event:
  - login audit
  - top-up requested
  - top-up confirmed
  - course purchase

### Giai đoạn 2: Nâng lên multiparty

- Chuyển sang `3 members`
- Tạo mapping identity thật cho từng user / role
- Tách audit/public/private data đúng nghĩa

## Các lệnh nên test sau khi stack chạy

```powershell
php artisan optimize:clear
```

Sau đó test trên web:

1. Đăng nhập thành công
2. Tạo yêu cầu nạp tiền
3. Xác nhận nạp tiền
4. Mua khóa học bằng ví
5. Xem lại metadata trong trang admin giao dịch nạp tiền

## Nguồn chính thức mình bám theo

- FireFly CLI install: https://hyperledger.github.io/firefly/latest/gettingstarted/firefly_cli/
- Start environment: https://hyperledger.github.io/firefly/v1.3.2/gettingstarted/setup_env/
- Sandbox: https://hyperledger.github.io/firefly/latest/gettingstarted/sandbox/
- Tokens tutorial: https://hyperledger.github.io/firefly/latest/tutorials/tokens/
- Broadcast data: https://hyperledger.github.io/firefly/latest/tutorials/broadcast_data/