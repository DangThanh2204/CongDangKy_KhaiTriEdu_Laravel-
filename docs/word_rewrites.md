# Đoạn văn cập nhật cho Word TTCK

> Em soạn lại các phần đã thay đổi trong code project. Anh/chị copy-paste vào Word, replace đoạn cũ tương ứng.
>
> Văn phong: cá nhân hóa (dùng "em"), bỏ các từ học thuật mòn ("tập trung", "tất cả", "tổng hợp", "đồng bộ", "linh hoạt"...). Section công nghệ chuyển sang gạch đầu dòng cho dễ đọc.

---

## (A) Thay đoạn "CƠ SỞ LÝ THUYẾT" — chuyển sang liệt kê

### Cơ sở lý thuyết

Để xây dựng hệ thống, em sử dụng các công nghệ và framework phổ biến trong phát triển web hiện đại. Mỗi công nghệ đảm nhận một vai trò cụ thể trong kiến trúc hệ thống. Phần dưới đây liệt kê những thành phần chính kèm vai trò của mỗi thành phần trong đề tài.

**Backend & framework**
- **PHP 8.2 + Laravel 12** — ngôn ngữ và framework chính cho phía máy chủ. Laravel cung cấp routing, middleware, Eloquent ORM (đã được tùy biến để làm việc với MongoDB) và hệ thống service container, giúp em viết mã theo mô hình MVC một cách rõ ràng.
- **Composer** — quản lý thư viện PHP, khóa version qua `composer.lock` để môi trường Render rebuild cho ra cùng kết quả với máy phát triển.

**Frontend**
- **Bootstrap 5.3** — framework CSS responsive. Em dùng grid system + utility class cho hầu hết bố cục (trang chủ, danh sách khóa học, dashboard admin), giảm thời gian viết CSS thuần.
- **Vite 7** — công cụ build frontend của Laravel 12. Hot Module Replacement (HMR) cho phép em xem ngay thay đổi CSS/JS khi phát triển; production build tự động minify, code-split và sinh hash vào tên file để cache busting.
- **Blade Template Engine** — engine view mặc định của Laravel. Em viết layout chung (`layouts/app.blade.php`, `layouts/admin.blade.php`) rồi extend cho từng trang cụ thể, kèm View Composer để chia sẻ dữ liệu site-wide (logo, tên trung tâm, footer).

**Cơ sở dữ liệu**
- **MongoDB Atlas (cluster M0 free tier, region GCP Singapore)** — lưu trữ dữ liệu chính. Mô hình document JSON-like phù hợp với schema có nhiều quan hệ lồng (course → modules → materials, enrollment → quiz attempts, ...). Em dùng package `mongodb/laravel-mongodb` để dùng Eloquent style trên Mongo.

**Xác thực & gửi mail**
- **OTP qua email** — sinh mã 6 chữ số, lưu vào collection `users` cùng `otp_sent_at` và TTL 10 phút. Hệ thống có rate-limit gửi lại + cooldown.
- **Brevo HTTPS API (`api.brevo.com`)** — dịch vụ gửi mail transactional, free 300 mail/ngày. Em chuyển từ Gmail SMTP sang Brevo do hạ tầng Render free chặn outbound TCP port 465/587, mọi kết nối SMTP đều timeout. API HTTPS qua port 443 không bị chặn nên gửi mail OTP ổn định.
- **OAuth 2.0 (Google, Facebook)** qua package `laravel/socialite` — học viên có thể đăng nhập bằng tài khoản mạng xã hội mà không cần đặt mật khẩu riêng cho hệ thống.

**Thanh toán**
- **VNPay Sandbox** — cổng thanh toán điện tử. Em sẽ trình bày chi tiết ở mục VNPay phía dưới.
- **Ví nội bộ** — collection `wallets` + `wallet_transactions`, mỗi học viên có một ví. Học viên nạp ví qua VNPay rồi dùng số dư để thanh toán học phí; lịch sử ghi đầy đủ vào `wallet_transactions`.

**AI Assistant**
- **Google Gemini 2.5 Flash Lite** qua REST API — em tích hợp trợ lý AI để trả lời câu hỏi về khóa học, hướng dẫn sử dụng. Context (kiến thức về trung tâm, danh mục khóa học) được nhồi vào system prompt khi gọi API.

**Triển khai**
- **Docker** — đóng gói Apache + PHP 8.2 + MongoDB extension + tài nguyên Vite đã build sẵn vào một image, đảm bảo môi trường giống nhau giữa máy phát triển và Render.
- **Render Web Service (free tier)** — hosting container, kết nối Atlas qua biến môi trường, auto-deploy mỗi commit lên `main`.

---

## (B) Thay đoạn "CÔNG NGHỆ TÍCH HỢP" — chuyển bullet, nổi bật VNPay

### Công nghệ tích hợp

Bên cạnh các công nghệ nền tảng, em tích hợp một số dịch vụ bên ngoài để mở rộng khả năng của hệ thống và rút ngắn thời gian phát triển. Mỗi dịch vụ giải quyết một bài toán cụ thể, được liệt kê dưới đây.

#### VNPay — cổng thanh toán điện tử

VNPay là cổng thanh toán phổ biến tại Việt Nam, hỗ trợ chuyển khoản qua tài khoản ngân hàng, ví VNPay và mã QR. Em chọn VNPay vì độ phủ ngân hàng nội địa rộng và có sandbox miễn phí cho việc thử nghiệm. Trong đề tài, VNPay đảm nhận hai luồng nghiệp vụ chính:

- **Nạp tiền vào ví nội bộ**: học viên chọn mệnh giá nạp → hệ thống sinh URL VNPay (đã ký HMAC-SHA512 với `VNPAY_HASH_SECRET`) → học viên hoàn tất thanh toán bên VNPay → VNPay gọi callback (Return URL + IPN URL) về hệ thống → em xác thực chữ ký, đối soát số tiền, cập nhật `wallet_transactions` và cộng số dư ví.
- **Thanh toán học phí trực tiếp**: trường hợp này hệ thống tạo bản ghi `payments` trạng thái `pending`, redirect học viên đến VNPay, sau khi callback thành công thì chuyển trạng thái `completed` và mở quyền học cho học viên.

Em chú ý các điểm bảo mật: ký mọi tham số gửi VNPay bằng HMAC-SHA512, xác minh chữ ký trên cả Return URL lẫn IPN, đối chiếu `vnp_TxnRef` với bản ghi `payments` nội bộ để tránh replay attack, và lưu raw callback vào `system_logs` để truy vết khi cần đối soát.

Cấu hình VNPay nằm trong các biến môi trường: `VNPAY_TMN_CODE` (mã định danh đơn vị), `VNPAY_HASH_SECRET` (khóa ký), `VNPAY_RETURN_URL`, `VNPAY_IPN_URL` (đều trỏ về domain Render của em). Sandbox URL hiện tại là `https://sandbox.vnpayment.vn/paymentv2/vpcpay.html` — chuyển sang production chỉ cần đổi URL và thay credentials thật.

#### Đăng nhập xã hội (OAuth 2.0)

Em tích hợp Google và Facebook OAuth qua `laravel/socialite`. Học viên bấm nút "Đăng nhập với Google/Facebook", hệ thống điều hướng tới trang xác thực của nhà cung cấp; sau khi học viên đồng ý, hệ thống nhận callback kèm token, tự động tạo tài khoản (nếu chưa có) hoặc liên kết với tài khoản đã tồn tại theo email.

#### Trợ lý AI Gemini

Em dùng Google Gemini 2.5 Flash Lite qua REST API để xây dựng trợ lý hội thoại. Trợ lý được nhồi sẵn ngữ cảnh về trung tâm, danh sách khóa học và hướng dẫn sử dụng, nhờ đó học viên có thể hỏi đáp thông tin cơ bản 24/7 mà không cần can thiệp của nhân viên.

#### Brevo — gửi mail OTP qua HTTPS API

Hệ thống cần gửi mail OTP cho đăng ký tài khoản, quên mật khẩu và thông báo duyệt đăng ký khóa học. Em chuyển từ Gmail SMTP sang Brevo HTTPS API vì hạ tầng Render free chặn outbound TCP port 465 và 587 (SMTP), mọi kết nối SMTP đều bị timeout. Brevo gọi qua HTTPS port 443 nên không bị chặn, miễn phí 300 mail/ngày — đủ cho phạm vi đồ án.

---

## (C) Thay đoạn "Flow đăng ký khóa học" (Hình 3.21)

> Đoạn cũ chỉ nói "giữ chỗ 24h" và không đề cập 1-click. Code hiện tại có 2 nhánh:

Quy trình đăng ký khóa học của em chia thành hai luồng tùy theo số dư ví của học viên tại thời điểm bấm "Đăng ký":

- **Học viên ví đủ tiền (1-click)**: hệ thống trừ ví, tạo bản ghi `payments` đã hoàn tất, đồng thời tạo `course_enrollment` thẳng ở trạng thái `approved`. Học viên chỉ cần một lần submit duy nhất là hoàn tất ghi danh, không phải qua bước trung gian.
- **Học viên ví chưa đủ**: hệ thống tạo `course_enrollment` ở trạng thái `pending` cùng với `seat_hold_expires_at = now + 48h` (giờ giữ chỗ đọc từ `Setting offline_seat_hold_hours`, mặc định 48). Học viên có 48 giờ để nạp ví; trong khoảng thời gian này, chỗ học vẫn được giữ và lớp không bị tăng người. Học viên nạp ví đủ rồi bấm "Xác nhận thanh toán" — hệ thống trừ ví và chuyển `enrollment` sang `approved`. Quá hạn mà chưa thanh toán, scheduler `enrollments:expire-seat-holds` (chạy mỗi 5 phút) tự hủy hồ sơ, giải phóng chỗ cho học viên khác trong hàng chờ.

Trường hợp lớp đã đủ chỗ, hệ thống đưa học viên vào `waitlist` (trạng thái `waitlist_joined`); khi có chỗ trống do người trước hủy hoặc hết hạn giữ chỗ, scheduler tự động chuyển học viên đầu hàng chờ sang trạng thái giữ chỗ và gửi mail thông báo.

Việc bỏ bước trung gian "chờ admin duyệt" đối với học viên đã thanh toán được điều khiển bằng `Setting offline_auto_approve_after_payment` (mặc định bật). Em thiết kế setting này để admin có thể bật/tắt — trường hợp khóa học cần kiểm tra hồ sơ đặc biệt (ví dụ kiểm tra đầu vào năng lực ngoại ngữ), admin chỉ cần tắt setting và mọi đăng ký sẽ về trạng thái `pending` chờ duyệt như cũ.

---

## (D) Thay đoạn "Xác thực OTP" / "Cổng thanh toán VNPay và ví điện tử nội bộ" trong CƠ SỞ LÝ THUYẾT

### Xác thực OTP qua email và đăng nhập bằng OAuth

OTP (One-Time Password) là mã xác thực ngắn hạn được sinh duy nhất cho mỗi phiên xác minh. Em sử dụng OTP để xác thực email người dùng tại bước đăng ký tài khoản và khôi phục mật khẩu. Mã OTP gồm 6 chữ số, có thời gian sống 10 phút và được gửi qua email người dùng.

Việc gửi mail OTP do dịch vụ **Brevo** đảm nhận, gọi qua HTTPS API (`api.brevo.com`). Em ban đầu cấu hình SMTP Gmail nhưng hạ tầng Render free chặn outbound port 465/587, dẫn tới mọi kết nối SMTP đều timeout. Brevo dùng HTTPS port 443 nên không bị chặn, đồng thời gói miễn phí 300 mail/ngày phù hợp cho phạm vi đồ án.

Bên cạnh OTP, em tích hợp **OAuth 2.0** với Google và Facebook qua thư viện `laravel/socialite`. Người dùng có thể đăng nhập bằng tài khoản mạng xã hội đã có, hệ thống tự khớp với tài khoản trong database theo email hoặc tạo mới nếu chưa có. Cách này rút ngắn quá trình đăng ký, đồng thời người dùng không phải đặt thêm mật khẩu riêng.

---

## (E) Thay đoạn "TRIỂN KHAI HỆ THỐNG"

### Triển khai hệ thống

Em triển khai hệ thống trên nền tảng đám mây **Render** (gói Web Service free), kết nối tới **MongoDB Atlas** (cluster M0 free, region GCP Singapore — `asia-southeast1`) để lưu dữ liệu, và gọi các dịch vụ ngoài qua HTTPS:

- **VNPay Sandbox** — thanh toán và nạp ví
- **Brevo HTTPS API** — gửi mail OTP và thông báo
- **Google Gemini API** — trợ lý AI
- **Google / Facebook OAuth** — đăng nhập xã hội

Toàn bộ ứng dụng được đóng gói bằng **Docker** (image dựa trên `php:8.2-apache`), bao gồm Apache, PHP 8.2 với extension `mongodb`, mã nguồn Laravel sau khi `composer install --no-dev` và bản build frontend của Vite. Cách đóng gói này giúp môi trường máy em phát triển và môi trường Render giống nhau, hạn chế lỗi do khác biệt cấu hình.

Khi em push commit lên nhánh `main` trên GitHub, Render tự nhận webhook, build image mới và rolling deploy. Quá trình này mất khoảng 1-2 phút mỗi lần. Cấu hình hệ thống (database URI, API keys, OAuth credentials, ...) đều nằm trong biến môi trường trên Render Dashboard, em không commit thông tin nhạy cảm vào Git.

Hệ thống có một scheduled job duy nhất là `enrollments:expire-seat-holds` (chạy mỗi 5 phút qua Laravel Scheduler), đảm nhận việc dọn dẹp các seat hold quá hạn và promote người trong hàng chờ. Job này được Render kích hoạt qua command `php artisan schedule:work` trong start script.

Lưu ý về giới hạn của gói free: Render free tier không có ổ đĩa bền (uploads bị mất sau mỗi lần redeploy) và chặn outbound SMTP (không gửi mail qua Gmail/Yahoo SMTP được). Hai điểm này em đã xử lý: ảnh upload tạm thời chấp nhận mất sau redeploy (phạm vi demo), và mail chuyển sang HTTPS API qua Brevo.

---

## (F) Bổ sung tính năng quản lý chứng chỉ — chèn vào "Phân tích chức năng" hoặc "Mô tả hoạt động"

Trong phân hệ quản trị, em xây dựng module **quản lý chứng chỉ học viên** (truy cập qua menu sidebar admin). Module này gồm:

- Danh sách học viên đã hoàn thành khóa học, kèm bộ lọc theo khóa, theo tình trạng cấp/chưa cấp chứng chỉ và tìm kiếm theo tên/email.
- Ba thẻ thống kê: tổng số học viên đã hoàn thành, số chứng chỉ đã cấp, số chứng chỉ chưa cấp.
- Hành động trên mỗi dòng: cấp chứng chỉ thủ công cho học viên đã hoàn thành, thu hồi chứng chỉ đã cấp, hoặc mở trang xác thực chứng chỉ công khai để xem.
- Xuất danh sách ra file Excel để báo cáo cho lãnh đạo trung tâm.

Mỗi chứng chỉ có một mã duy nhất theo định dạng `KTE-YYYYMMDD-XXXXXX` (sinh ngẫu nhiên 6 ký tự). Mã này có thể tra cứu công khai tại đường dẫn `/xac-thuc-chung-chi?code=...` — bất kỳ ai (nhà tuyển dụng, đối tác) cũng kiểm tra được tính hợp lệ của chứng chỉ mà không cần đăng nhập.

---

## (G) Các từ kiêng kỵ cần thay khi rà Word

Em tìm thấy các từ này trong bản TTCK (3) — anh/chị Find & Replace trong Word:

| Từ cũ | Đề xuất thay |
|---|---|
| "tập trung" | "ưu tiên" / "đầu tư công sức vào" / bỏ hẳn |
| "tất cả" | "toàn bộ" / "phần lớn" / liệt kê cụ thể |
| "tổng hợp" (khi không phải động từ tổng hợp dữ liệu) | "kết hợp" / "vận dụng" |
| "đồng bộ" (khi không nói về đồng bộ dữ liệu) | "thống nhất" / "nhất quán" |
| "linh hoạt" | bỏ hoặc thay bằng "có thể tùy biến" / "phù hợp với từng trường hợp" |
| "đa dạng" | liệt kê cụ thể các trường hợp |
| "hiện đại" / "tiên tiến" | bỏ — đây là tính từ marketing, không có nội dung |
| "đáng kể" | thay bằng số liệu cụ thể nếu có |
| "nói chung" / "nhìn chung" | bỏ — câu kết luận không cần mở đầu chung chung |

---

## Hướng dẫn áp dụng

1. Mở `WordTTCK_updated.docx` (file vừa thay 18 ảnh sơ đồ mới).
2. Với mỗi đoạn (A) → (F): tìm đoạn cũ tương ứng trong Word, copy đoạn mới ở đây paste đè lên. Sau đó format lại heading + bullet trong Word (dùng bullet list mặc định của Word, không paste markdown thô).
3. Bullet hiện trong markdown này dùng dấu `-`. Khi dán vào Word, anh/chị format thành bullet Word (Home → Bullets) cho thống nhất.
4. Mục (G): mở Find & Replace (`Ctrl+H`) trong Word, sửa từng từ.

Em không edit trực tiếp file Word vì thao tác đó dễ phá format heading/style/footer của thầy cô yêu cầu. Tự copy-paste vào Word + giữ format có sẵn là an toàn nhất.
