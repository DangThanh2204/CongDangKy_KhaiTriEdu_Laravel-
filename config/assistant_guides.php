<?php

/*
 * Knowledge base các thao tác trên website Khai Trí Edu cho trợ lý AI.
 *
 * Mỗi guide gồm:
 *  - title: tên thao tác (in trong prompt)
 *  - topic: phân loại (auth, wallet, enrollment, learning, certificate, profile, support, ...)
 *  - keywords: từ khóa đã chuẩn hóa (lowercase, ascii, không dấu) để match câu hỏi
 *  - url_patterns: regex match path hiện tại của user — ưu tiên guide cùng trang
 *  - route: route name (nếu có) để trợ lý dẫn link bằng URL thực
 *  - steps: các bước hướng dẫn ngắn gọn
 *  - notes: lưu ý / edge case
 */

return [
    'guides' => [
        'register-account' => [
            'title' => 'Đăng ký tài khoản mới',
            'topic' => 'auth',
            'keywords' => [
                'dang ky tai khoan', 'tao tai khoan', 'sign up', 'mo tai khoan',
                'tao acc', 'tao account', 'dang ky thanh vien', 'kich hoat tai khoan',
                'nhap otp', 'ma otp', 'xac thuc email',
            ],
            'url_patterns' => ['#^/register#', '#^/verify#'],
            'route' => 'register',
            'steps' => [
                'Nhấn nút "Đăng ký" ở góc trên bên phải hoặc vào trang /register.',
                'Điền họ tên, email, mật khẩu và xác nhận mật khẩu, sau đó bấm "Đăng ký".',
                'Hệ thống gửi mã OTP về email; nhập mã tại trang /verify để kích hoạt tài khoản.',
                'Nếu không nhận được mã, kiểm tra hộp thư rác hoặc bấm "Gửi lại mã" trên trang /verify.',
            ],
            'notes' => [
                'Có thể đăng ký nhanh bằng Google hoặc Facebook ngay tại trang /login.',
                'Email phải hợp lệ và chưa được dùng cho tài khoản khác.',
            ],
        ],

        'login' => [
            'title' => 'Đăng nhập',
            'topic' => 'auth',
            'keywords' => [
                'dang nhap', 'login', 'sign in', 'vao tai khoan', 'truy cap tai khoan',
                'dang nhap google', 'dang nhap facebook', 'oauth',
            ],
            'url_patterns' => ['#^/login#'],
            'route' => 'login',
            'steps' => [
                'Vào trang /login, nhập email và mật khẩu rồi bấm "Đăng nhập".',
                'Hoặc bấm nút "Đăng nhập với Google" / "Đăng nhập với Facebook" để dùng tài khoản mạng xã hội.',
                'Nếu sai mật khẩu nhiều lần hoặc bị khóa, thử lại sau hoặc dùng "Quên mật khẩu".',
            ],
            'notes' => [
                'Tài khoản chưa kích hoạt sẽ được điều hướng sang /verify để nhập OTP.',
            ],
        ],

        'forgot-password' => [
            'title' => 'Quên mật khẩu / đặt lại mật khẩu',
            'topic' => 'auth',
            'keywords' => [
                'quen mat khau', 'lay lai mat khau', 'reset mat khau', 'doi mat khau quen',
                'forgot password', 'khong nho mat khau', 'dat lai mat khau',
            ],
            'url_patterns' => ['#^/forgot-password#'],
            'route' => 'password.forgot',
            'steps' => [
                'Vào trang /forgot-password và nhập email đã đăng ký.',
                'Hệ thống gửi mã OTP đặt lại mật khẩu về email; nhập mã ở trang đặt lại.',
                'Nhập mật khẩu mới (tối thiểu theo yêu cầu hệ thống) và xác nhận để hoàn tất.',
            ],
            'notes' => [
                'Mã OTP có thời hạn ngắn, nếu hết hạn hãy yêu cầu mã mới.',
            ],
        ],

        'profile-edit' => [
            'title' => 'Cập nhật hồ sơ cá nhân',
            'topic' => 'profile',
            'keywords' => [
                'cap nhat ho so', 'sua ho so', 'doi thong tin', 'thay anh dai dien',
                'avatar', 'profile', 'thong tin ca nhan', 'sua thong tin',
            ],
            'url_patterns' => ['#^/profile#'],
            'route' => 'profile.edit',
            'steps' => [
                'Sau khi đăng nhập, vào menu hồ sơ hoặc truy cập /profile.',
                'Bấm "Chỉnh sửa hồ sơ" để vào /profile/edit và cập nhật họ tên, ảnh, số điện thoại, ngày sinh...',
                'Bấm "Lưu" để áp dụng thay đổi.',
            ],
            'notes' => [
                'Đổi mật khẩu nằm ở mục riêng /profile/change-password.',
            ],
        ],

        'change-password' => [
            'title' => 'Đổi mật khẩu (đã đăng nhập)',
            'topic' => 'profile',
            'keywords' => [
                'doi mat khau', 'change password', 'thay mat khau', 'cap nhat mat khau',
            ],
            'url_patterns' => ['#^/profile/change-password#'],
            'route' => 'profile.change-password.form',
            'steps' => [
                'Đăng nhập, vào /profile/change-password.',
                'Nhập mật khẩu hiện tại, sau đó nhập mật khẩu mới và xác nhận lại.',
                'Bấm "Cập nhật" để hoàn tất.',
            ],
        ],

        'wallet-overview' => [
            'title' => 'Xem ví và lịch sử giao dịch',
            'topic' => 'wallet',
            'keywords' => [
                'vi cua toi', 'so du vi', 'xem vi', 'wallet', 'giao dich vi',
                'lich su nap tien', 'lich su giao dich vi',
            ],
            'url_patterns' => ['#^/wallet#'],
            'route' => 'wallet.index',
            'steps' => [
                'Đăng nhập rồi mở menu "Ví của tôi" hoặc vào /wallet.',
                'Trang ví hiển thị số dư hiện tại và danh sách giao dịch gần đây.',
                'Bấm phân trang để xem các giao dịch cũ hơn.',
            ],
        ],

        'wallet-topup-vnpay' => [
            'title' => 'Nạp ví qua VNPay',
            'topic' => 'wallet',
            'keywords' => [
                'vnpay', 'nap vi vnpay', 'nap tien vnpay', 'topup vnpay', 'thanh toan vnpay nap vi',
                'nap qua the', 'nap qua atm', 'cong vnpay', 'nap vi qua vnpay',
            ],
            'url_patterns' => ['#^/wallet#'],
            'route' => 'wallet.index',
            'steps' => [
                'Vào /wallet, chọn tab/khu vực "Nạp tiền".',
                'Nhập số tiền muốn nạp (tối thiểu 1.000đ, tối đa 10.000.000đ) và chọn phương thức "VNPay".',
                'Bấm "Nạp tiền" — hệ thống sẽ điều hướng sang cổng VNPay để hoàn tất thanh toán.',
                'Sau khi VNPay báo thành công, số dư ví được cộng tự động và bạn quay về /wallet.',
            ],
            'notes' => [
                'Nếu cổng VNPay chưa được cấu hình hoặc gặp lỗi, hãy thử phương thức "Chuyển khoản ngân hàng".',
            ],
        ],

        'wallet-topup-bank' => [
            'title' => 'Nạp ví bằng chuyển khoản ngân hàng',
            'topic' => 'wallet',
            'keywords' => [
                'nap vi chuyen khoan', 'nap tien ngan hang', 'qr nap tien', 'topup bank',
                'chuyen khoan nap vi', 'qr code nap vi', 'vietqr',
            ],
            'url_patterns' => ['#^/wallet#'],
            'route' => 'wallet.index',
            'steps' => [
                'Vào /wallet và chọn phương thức "Chuyển khoản ngân hàng" trong khu vực Nạp tiền.',
                'Nhập số tiền và bấm tạo yêu cầu — hệ thống hiện thông tin tài khoản nhận và mã nội dung chuyển khoản (QR VietQR).',
                'Mở app ngân hàng, quét QR hoặc chuyển đúng số tiền và đúng nội dung được hiển thị.',
                'Quay lại /wallet và bấm "Tôi đã chuyển" để admin đối soát; số dư cộng sau khi xác nhận.',
            ],
            'notes' => [
                'Phải chuyển đúng nội dung trong yêu cầu, sai nội dung sẽ làm chậm việc đối soát.',
                'Tính năng này chỉ hiển thị khi admin đã cấu hình thông tin ngân hàng.',
            ],
        ],

        'wallet-topup-direct' => [
            'title' => 'Nạp ví trực tiếp tại quầy',
            'topic' => 'wallet',
            'keywords' => [
                'nap vi tai quay', 'nap truc tiep', 'ma nap', 'nap tien tai trung tam',
                'nap tien mat',
            ],
            'url_patterns' => ['#^/wallet#'],
            'route' => 'wallet.index',
            'steps' => [
                'Vào /wallet, chọn phương thức "Nạp trực tiếp tại quầy".',
                'Nhập số tiền muốn nạp và bấm tạo yêu cầu — hệ thống sinh mã nạp có hiệu lực 24 giờ.',
                'Mang mã này tới quầy tiếp tân/nhân viên để nộp tiền mặt; nhân viên sẽ xác nhận và cộng số dư.',
            ],
            'notes' => [
                'Mã quá hạn sẽ tự hủy, lúc đó cần tạo yêu cầu mới.',
            ],
        ],

        'enroll-course-online' => [
            'title' => 'Đăng ký khóa học online',
            'topic' => 'enrollment',
            'keywords' => [
                'dang ky khoa hoc', 'enroll', 'mua khoa hoc', 'tham gia khoa hoc',
                'dang ky hoc online', 'thanh toan khoa hoc', 'mua bang vi',
            ],
            'url_patterns' => ['#^/courses/[^/]+$#'],
            'route' => 'courses.index',
            'steps' => [
                'Vào /courses để duyệt, hoặc mở trang chi tiết khóa học muốn đăng ký.',
                'Bấm nút "Đăng ký học" trên trang chi tiết khóa học.',
                'Nếu khóa có học phí, hệ thống trừ tiền từ ví — đảm bảo số dư /wallet đủ trước khi đăng ký.',
                'Đăng ký thành công bạn có thể vào học ngay; nếu khóa cần admin duyệt, trạng thái sẽ là "Chờ duyệt".',
            ],
            'notes' => [
                'Khóa học trả phí hiện chỉ hỗ trợ thanh toán bằng số dư ví; nếu chưa đủ, hãy nạp ví trước.',
                'Một số khóa online thuộc cùng "series" được tự động miễn phí nếu bạn đã mua khóa cùng series.',
                'Có thể nhập mã giảm giá (voucher) trước khi xác nhận đăng ký để giảm học phí.',
            ],
        ],

        'enroll-course-offline' => [
            'title' => 'Đăng ký khóa học offline (chọn đợt, giữ chỗ 24h, hàng chờ)',
            'topic' => 'enrollment',
            'keywords' => [
                'dang ky offline', 'lop offline', 'chon dot hoc', 'lich khai giang',
                'giu cho', 'hang cho', 'waitlist', 'doi dot hoc', 'chon lop',
            ],
            'url_patterns' => ['#^/courses/[^/]+$#', '#^/lich-khai-giang#'],
            'route' => 'courses.intakes',
            'steps' => [
                'Vào /lich-khai-giang hoặc trang chi tiết khóa học và chọn đợt học (có lịch khai giảng cụ thể).',
                'Bấm "Đăng ký học" — nếu còn chỗ và khóa có phí, ví sẽ bị trừ tiền và đăng ký được tạo.',
                'Nếu đợt đã đầy, hệ thống đưa bạn vào "Hàng chờ" và sẽ giữ chỗ 24h khi có người hủy; bạn cần xác nhận trong 24h này để hoàn tất.',
                'Trạng thái đăng ký xem ở /ho-so-cua-toi.',
            ],
            'notes' => [
                'Có thể đổi sang đợt học khác chưa bắt đầu trên trang Hồ sơ học viên (nếu admin cho phép đổi và còn chỗ).',
                'Hủy đăng ký trước khi đợt bắt đầu sẽ được hoàn tiền vào ví.',
            ],
        ],

        'unenroll-course' => [
            'title' => 'Hủy đăng ký khóa học và hoàn tiền',
            'topic' => 'enrollment',
            'keywords' => [
                'huy dang ky', 'huy khoa hoc', 'roi hang cho', 'huy giu cho',
                'hoan tien khoa hoc', 'refund',
            ],
            'route' => 'student.application-status',
            'steps' => [
                'Vào /ho-so-cua-toi để xem các đăng ký đang có.',
                'Chọn khóa cần hủy và bấm "Hủy đăng ký".',
                'Nếu đợt học chưa bắt đầu, học phí sẽ được hoàn vào ví của bạn (không hoàn nếu đợt đã khai giảng).',
            ],
            'notes' => [
                'Đăng ký đã hoàn thành hoặc bị từ chối thì không thể hủy.',
            ],
        ],

        'learn-course' => [
            'title' => 'Vào học khóa học (xem video, tài liệu, đánh dấu hoàn thành)',
            'topic' => 'learning',
            'keywords' => [
                'vao hoc', 'xem video', 'hoc bai', 'lop hoc cua toi', 'noi dung hoc',
                'tai lieu khoa hoc', 'video khoa hoc', 'classroom',
            ],
            'url_patterns' => ['#^/courses/[^/]+/learn#'],
            'route' => 'courses.learn',
            'steps' => [
                'Sau khi đăng ký được duyệt, vào /dashboard hoặc /ho-so-cua-toi và bấm "Vào học".',
                'Trang học hiển thị danh sách module/tài liệu/video — chọn từng mục để học theo thứ tự.',
                'Sau khi xem xong tài liệu hoặc video, hệ thống ghi nhận tiến độ; làm quiz nếu module có yêu cầu.',
            ],
        ],

        'take-quiz' => [
            'title' => 'Làm bài quiz / kiểm tra',
            'topic' => 'learning',
            'keywords' => [
                'quiz', 'lam bai quiz', 'lam kiem tra', 'bai test', 'lam bai test',
                'cau hoi trac nghiem', 'attempt quiz',
            ],
            'route' => 'courses.learn',
            'steps' => [
                'Trong trang Vào học, mở module có chứa quiz và bấm "Bắt đầu làm bài".',
                'Trả lời từng câu, hệ thống lưu đáp án tạm thời; bạn có thể quay lại nếu chưa hết thời gian.',
                'Bấm "Nộp bài" để chấm điểm; điểm và đáp án đúng sẽ được hiển thị (tùy cấu hình).',
            ],
            'notes' => [
                'Quiz có thể giới hạn thời gian hoặc số lần làm; xem mô tả quiz trước khi bắt đầu.',
            ],
        ],

        'certificate-view' => [
            'title' => 'Xem hoặc tải chứng chỉ sau khi hoàn thành',
            'topic' => 'certificate',
            'keywords' => [
                'chung chi', 'tai chung chi', 'lay chung chi', 'certificate', 'in chung chi',
                'chung chi hoan thanh',
            ],
            'route' => 'student.application-status',
            'steps' => [
                'Hoàn thành đầy đủ tài liệu và quiz trong khóa học, hệ thống cấp chứng chỉ.',
                'Vào /ho-so-cua-toi, mở khóa đã hoàn thành và bấm "Xem chứng chỉ" để tải.',
            ],
            'notes' => [
                'Chứng chỉ chỉ cấp cho khóa đã đạt điều kiện hoàn thành (xem yêu cầu trong mô tả khóa).',
            ],
        ],

        'certificate-verify' => [
            'title' => 'Xác thực / tra cứu chứng chỉ (công khai)',
            'topic' => 'certificate',
            'keywords' => [
                'xac thuc chung chi', 'tra cuu chung chi', 'kiem tra chung chi',
                'verify certificate', 'check chung chi',
            ],
            'url_patterns' => ['#^/xac-thuc-chung-chi#'],
            'route' => 'certificates.verify',
            'steps' => [
                'Truy cập /xac-thuc-chung-chi (không cần đăng nhập).',
                'Nhập mã chứng chỉ in trên chứng chỉ và bấm "Tra cứu".',
                'Hệ thống hiển thị thông tin học viên, khóa học và ngày cấp nếu mã hợp lệ.',
            ],
        ],

        'payment-history' => [
            'title' => 'Xem lịch sử thanh toán',
            'topic' => 'wallet',
            'keywords' => [
                'lich su thanh toan', 'lich su mua khoa hoc', 'hoa don',
                'xem hoa don', 'bien lai', 'receipt',
            ],
            'url_patterns' => ['#^/lich-su-thanh-toan#'],
            'route' => 'student.payments.index',
            'steps' => [
                'Đăng nhập, vào menu "Lịch sử thanh toán" hoặc /lich-su-thanh-toan.',
                'Mỗi giao dịch hiển thị khóa học, số tiền, phương thức và trạng thái.',
                'Bấm vào giao dịch để xem chi tiết và tải biên lai PDF (nếu có).',
            ],
        ],

        'application-status' => [
            'title' => 'Xem trạng thái đăng ký / hồ sơ học viên',
            'topic' => 'enrollment',
            'keywords' => [
                'ho so cua toi', 'don dang ky', 'cho duyet', 'trang thai dang ky',
                'application status', 'don hoc',
            ],
            'url_patterns' => ['#^/ho-so-cua-toi#', '#^/dashboard#'],
            'route' => 'student.application-status',
            'steps' => [
                'Vào /dashboard (trang tổng quan) hoặc /ho-so-cua-toi để xem các đăng ký.',
                'Mỗi đăng ký hiển thị trạng thái: chờ duyệt, đã duyệt, hoàn thành, hàng chờ, giữ chỗ 24h, đã hủy.',
                'Bấm "Vào học" với đăng ký đã được duyệt, hoặc "Xác nhận giữ chỗ" trong 24h nếu được mời từ hàng chờ.',
            ],
        ],

        'notifications' => [
            'title' => 'Xem và đánh dấu đã đọc thông báo',
            'topic' => 'support',
            'keywords' => [
                'thong bao', 'notification', 'tin moi', 'da doc thong bao',
                'mark all read',
            ],
            'url_patterns' => ['#^/notifications#'],
            'route' => 'notifications.index',
            'steps' => [
                'Bấm icon chuông trên thanh menu hoặc vào /notifications để xem thông báo.',
                'Bấm vào thông báo để mở chi tiết hoặc đi tới trang liên quan.',
                'Bấm "Đánh dấu tất cả đã đọc" để dọn sạch danh sách chưa đọc.',
            ],
        ],

        'browse-courses' => [
            'title' => 'Tìm và duyệt khóa học',
            'topic' => 'enrollment',
            'keywords' => [
                'tim khoa hoc', 'duyet khoa hoc', 'danh sach khoa hoc', 'browse courses',
                'khoa hoc co san', 'lich khai giang',
            ],
            'url_patterns' => ['#^/courses#', '#^/lich-khai-giang#'],
            'route' => 'courses.index',
            'steps' => [
                'Vào /courses để xem toàn bộ khóa học, dùng bộ lọc theo nhóm ngành, hình thức, trình độ, giá.',
                'Hoặc /lich-khai-giang để xem riêng các đợt học offline đang mở.',
                'Bấm vào tên khóa để xem chi tiết, đánh giá, lịch học và đăng ký.',
            ],
        ],

        'contact-support' => [
            'title' => 'Liên hệ hỗ trợ',
            'topic' => 'support',
            'keywords' => [
                'lien he', 'ho tro', 'support', 'so dien thoai', 'email lien he',
                'dia chi trung tam', 'gui lien he',
            ],
            'url_patterns' => ['#^/contact#'],
            'route' => 'contact',
            'steps' => [
                'Vào /contact để xem số điện thoại, email và địa chỉ trung tâm.',
                'Có thể gửi tin nhắn trực tiếp qua form trên trang Liên hệ; bộ phận tư vấn sẽ phản hồi qua email.',
            ],
        ],
    ],
];
