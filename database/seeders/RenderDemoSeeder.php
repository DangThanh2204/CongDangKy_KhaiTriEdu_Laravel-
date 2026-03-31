<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseMaterial;
use App\Models\CourseModule;
use App\Models\Payment;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class RenderDemoSeeder extends Seeder
{
    private const DEMO_PASSWORD = 'Demo@123';

    public function run(): void
    {
        $users = $this->seedUsers();
        $catalog = $this->seedCatalog($users);
        $enrollments = $this->seedEnrollments($users, $catalog);

        $this->seedLearningProgress($users, $catalog, $enrollments);
        $this->seedPayments($users, $catalog);
        $this->seedWallets($users, $catalog);
        $this->seedPosts($users);
        $this->seedSettings();
        $this->syncCourseMetrics($catalog['courses']);
    }

    private function seedUsers(): array
    {
        return [
            'admin' => $this->upsertUser('admin@khaitri.edu.vn', 'admin', 'Quản trị viên Khai Trí', 'admin'),
            'instructor' => $this->upsertUser('giangvien@khaitri.edu.vn', 'giangvien', 'Giảng viên Nguyễn Minh An', 'instructor'),
            'student_one' => $this->upsertUser('hocvien1@khaitri.edu.vn', 'hocvien1', 'Học viên Trần Gia Hân', 'student'),
            'student_two' => $this->upsertUser('hocvien2@khaitri.edu.vn', 'hocvien2', 'Học viên Lê Quốc Bảo', 'student'),
            'student_three' => $this->upsertUser('hocvien3@khaitri.edu.vn', 'hocvien3', 'Học viên Phạm Khánh Linh', 'student'),
        ];
    }

    private function seedCatalog(array $users): array
    {
        $categories = [
            'it' => $this->upsertCategory('cong-nghe-thong-tin', 'Công nghệ thông tin', 'Nhóm ngành kỹ thuật số, lập trình và chuyển đổi số.', '#1d4ed8', 1),
            'marketing' => $this->upsertCategory('marketing-kinh-doanh', 'Marketing - Kinh doanh', 'Nhóm ngành về nội dung, tăng trưởng và bán hàng hiện đại.', '#f97316', 2),
            'english' => $this->upsertCategory('tieng-anh', 'Tiếng Anh', 'Nhóm ngành ngoại ngữ phục vụ học tập và công việc.', '#0f766e', 3),
        ];

        $courses = [
            'fullstack' => $this->upsertCourse('lap-trinh-web-fullstack-can-ban', [
                'title' => 'Lập trình Web Fullstack căn bản',
                'description' => 'Khóa học online giúp bạn đi từ HTML/CSS đến JavaScript và Laravel với lộ trình rõ ràng theo module.',
                'short_description' => 'Học online theo module, có video, quiz và bài tập thực hành.',
                'category_id' => $categories['it']->id,
                'instructor_id' => $users['instructor']->id,
                'price' => 2490000,
                'sale_price' => 1990000,
                'level' => 'beginner',
                'status' => 'published',
                'is_featured' => true,
                'is_popular' => true,
                'learning_type' => 'online',
                'announcement' => 'Đăng ký xong có thể vào học ngay trên hệ thống.',
                'students_count' => 0,
                'created_at' => Carbon::now()->subDays(24),
            ]),
            'marketing' => $this->upsertCourse('digital-marketing-thuc-chien-4-tuan', [
                'title' => 'Digital Marketing thực chiến 4 tuần',
                'description' => 'Khóa học online tập trung vào tư duy nội dung, quảng cáo và phân tích hiệu quả chiến dịch.',
                'short_description' => 'Khóa online ngắn hạn cho người mới bắt đầu làm marketing.',
                'category_id' => $categories['marketing']->id,
                'instructor_id' => $users['instructor']->id,
                'price' => 1890000,
                'sale_price' => 1490000,
                'level' => 'intermediate',
                'status' => 'published',
                'is_featured' => true,
                'is_popular' => false,
                'learning_type' => 'online',
                'announcement' => 'Có workshop live qua Meet và bài tập tình huống theo tuần.',
                'students_count' => 0,
                'created_at' => Carbon::now()->subDays(15),
            ]),
            'english' => $this->upsertCourse('tieng-anh-giao-tiep-b1', [
                'title' => 'Tiếng Anh giao tiếp B1',
                'description' => 'Khóa học offline tại trung tâm, có giáo viên theo sát, lịch học cố định và nhiều đợt khai giảng.',
                'short_description' => 'Khóa offline có lịch học, phòng học, giáo viên và số lượng chỗ rõ ràng.',
                'category_id' => $categories['english']->id,
                'instructor_id' => $users['instructor']->id,
                'price' => 3200000,
                'sale_price' => 2890000,
                'level' => 'intermediate',
                'status' => 'published',
                'is_featured' => false,
                'is_popular' => true,
                'learning_type' => 'offline',
                'announcement' => 'Khóa offline cần admin duyệt hồ sơ đăng ký trước khi vào học.',
                'students_count' => 0,
                'created_at' => Carbon::now()->subDays(7),
            ]),
        ];

        $classes = [
            'fullstack_main' => $this->upsertClass($courses['fullstack'], [
                'name' => 'Học ngay - Kỳ tháng 04',
                'instructor_id' => $users['instructor']->id,
                'start_date' => Carbon::now()->subDays(10)->toDateString(),
                'end_date' => Carbon::now()->addDays(50)->toDateString(),
                'schedule' => 'Tự học linh hoạt trên hệ thống',
                'meeting_info' => 'Workshop hỏi đáp mỗi thứ 7 lúc 20:00',
                'max_students' => 0,
                'status' => 'active',
                'created_at' => Carbon::now()->subDays(10),
            ]),
            'marketing_main' => $this->upsertClass($courses['marketing'], [
                'name' => 'Học ngay - Kỳ tháng 04',
                'instructor_id' => $users['instructor']->id,
                'start_date' => Carbon::now()->subDays(6)->toDateString(),
                'end_date' => Carbon::now()->addDays(28)->toDateString(),
                'schedule' => 'Tự học + workshop live tối thứ 5',
                'meeting_info' => 'Workshop live qua Google Meet lúc 19:30 thứ 5',
                'max_students' => 0,
                'status' => 'active',
                'created_at' => Carbon::now()->subDays(6),
            ]),
            'english_april' => $this->upsertClass($courses['english'], [
                'name' => 'Khóa 01 - Tháng 04',
                'instructor_id' => $users['instructor']->id,
                'start_date' => Carbon::now()->addDays(5)->toDateString(),
                'end_date' => Carbon::now()->addDays(65)->toDateString(),
                'schedule' => 'Thứ 2, 4, 6 · 18:30 - 20:00',
                'meeting_info' => 'Phòng B203 - Cơ sở Long Xuyên',
                'max_students' => 18,
                'status' => 'active',
                'created_at' => Carbon::now()->subDays(4),
            ]),
            'english_may' => $this->upsertClass($courses['english'], [
                'name' => 'Khóa 02 - Tháng 05',
                'instructor_id' => $users['instructor']->id,
                'start_date' => Carbon::now()->addDays(28)->toDateString(),
                'end_date' => Carbon::now()->addDays(88)->toDateString(),
                'schedule' => 'Thứ 3, 5, 7 · 18:30 - 20:00',
                'meeting_info' => 'Phòng A105 - Cơ sở Long Xuyên',
                'max_students' => 20,
                'status' => 'active',
                'created_at' => Carbon::now()->subDays(2),
            ]),
        ];
        $modules = [
            'fullstack_html' => $this->upsertModule($courses['fullstack'], 'HTML & CSS nền tảng', 'Làm quen cấu trúc trang web và cách dựng giao diện cơ bản.', 1),
            'fullstack_js' => $this->upsertModule($courses['fullstack'], 'JavaScript thực chiến', 'Xử lý tương tác, DOM và các bài toán frontend phổ biến.', 2),
            'fullstack_laravel' => $this->upsertModule($courses['fullstack'], 'Laravel căn bản', 'Làm việc với route, controller, blade và kết nối dữ liệu.', 3),
            'marketing_content' => $this->upsertModule($courses['marketing'], 'Content Strategy', 'Viết nội dung đúng insight và lên kế hoạch nội dung theo tuần.', 1),
            'marketing_ads' => $this->upsertModule($courses['marketing'], 'Ads & Performance', 'Đọc chỉ số quảng cáo, tối ưu chi phí và cải thiện chuyển đổi.', 2),
            'english_listening' => $this->upsertModule($courses['english'], 'Nghe', 'Luyện nghe tình huống giao tiếp trong công việc và đời sống.', 1),
            'english_speaking' => $this->upsertModule($courses['english'], 'Nói', 'Tăng phản xạ giao tiếp và trình bày ý kiến tự tin hơn.', 2),
            'english_reading' => $this->upsertModule($courses['english'], 'Đọc', 'Đọc hiểu email, thông báo và văn bản tiếng Anh trình độ B1.', 3),
            'english_writing' => $this->upsertModule($courses['english'], 'Viết', 'Viết email và đoạn văn ngắn phục vụ học tập, công việc.', 4),
        ];

        $materials = [
            'fullstack_video' => $this->upsertMaterial($courses['fullstack'], $modules['fullstack_html'], 'video', 'Khởi động với HTML & CSS', 'Video định hướng và cách dựng bố cục giao diện đầu tiên.', 1, 25, ['url' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U']),
            'fullstack_assignment' => $this->upsertMaterial($courses['fullstack'], $modules['fullstack_html'], 'assignment', 'Bài tập dựng landing page cá nhân', 'Thực hành dựng landing page ngắn bằng HTML, CSS và responsive cơ bản.', 2, 20, ['content' => 'Hoàn thiện landing page giới thiệu bản thân với 3 section và gửi link source code.']),
            'fullstack_quiz' => $this->upsertMaterial($courses['fullstack'], $modules['fullstack_js'], 'quiz', 'Quiz JavaScript căn bản', 'Kiểm tra kiến thức DOM, event và biến trong JavaScript.', 3, 15, ['passing_score' => 70, 'questions' => [['question' => 'DOM viết tắt của cụm từ nào?', 'answer' => 'Document Object Model'], ['question' => 'Event dùng khi người dùng bấm nút là gì?', 'answer' => 'click']]]),
            'fullstack_laravel_video' => $this->upsertMaterial($courses['fullstack'], $modules['fullstack_laravel'], 'video', 'Laravel route, controller và blade', 'Đi qua luồng xử lý request và cách render giao diện trong Laravel.', 4, 35, ['url' => 'https://www.youtube.com/watch?v=ImtZ5yENzgE']),
            'marketing_video' => $this->upsertMaterial($courses['marketing'], $modules['marketing_content'], 'video', 'Tư duy content chuyển đổi', 'Cách xác định insight, hook và CTA trong nội dung bán hàng.', 1, 22, ['url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw']),
            'marketing_assignment' => $this->upsertMaterial($courses['marketing'], $modules['marketing_content'], 'assignment', 'Bài tập lập kế hoạch nội dung 7 ngày', 'Thiết kế lịch nội dung ngắn hạn cho fanpage hoặc kênh TikTok.', 2, 18, ['content' => 'Xây dựng lịch nội dung 7 ngày với mục tiêu tăng lead và tỉ lệ inbox.']),
            'marketing_quiz' => $this->upsertMaterial($courses['marketing'], $modules['marketing_ads'], 'quiz', 'Quiz chỉ số quảng cáo', 'Ôn tập các chỉ số CTR, CPC và conversion rate.', 3, 12, ['passing_score' => 75, 'questions' => [['question' => 'CTR là viết tắt của gì?', 'answer' => 'Click Through Rate'], ['question' => 'CPC thường được hiểu là gì?', 'answer' => 'Cost Per Click']]]),
            'marketing_meeting' => $this->upsertMaterial($courses['marketing'], $modules['marketing_ads'], 'meeting', 'Workshop live tối ưu chiến dịch', 'Buổi live phân tích case thật và tối ưu chiến dịch trực tiếp cùng giảng viên.', 4, 90, ['meeting_url' => 'https://meet.google.com/abc-demo-khai-tri', 'meeting_starts_at' => Carbon::now()->subDays(2)->setTime(19, 30)->toDateTimeString(), 'meeting_ends_at' => Carbon::now()->subDays(2)->setTime(21, 0)->toDateTimeString(), 'meeting_note' => 'Mang theo câu hỏi hoặc chiến dịch bạn đang chạy để được góp ý trực tiếp.']),
        ];

        return ['courses' => $courses, 'classes' => $classes, 'modules' => $modules, 'materials' => $materials];
    }

    private function seedEnrollments(array $users, array $catalog): array
    {
        return [
            'student_one_fullstack' => $this->upsertEnrollment($users['student_one'], $catalog['classes']['fullstack_main'], 'completed', Carbon::now()->subDays(18), Carbon::now()->subDays(18), Carbon::now()->subDays(5), 'Hoàn thành khóa học và đủ điều kiện cấp chứng chỉ.'),
            'student_two_marketing' => $this->upsertEnrollment($users['student_two'], $catalog['classes']['marketing_main'], 'approved', Carbon::now()->subDays(8), Carbon::now()->subDays(8), null, 'Đang học giữa kỳ và đã mở quyền học ngay.'),
            'student_two_english_pending' => $this->upsertEnrollment($users['student_two'], $catalog['classes']['english_april'], 'pending', Carbon::now()->subDays(1), null, null, 'Chờ admin duyệt hồ sơ cho đợt học offline.'),
            'student_three_fullstack' => $this->upsertEnrollment($users['student_three'], $catalog['classes']['fullstack_main'], 'completed', Carbon::now()->subDays(20), Carbon::now()->subDays(20), Carbon::now()->subDays(7), 'Đã hoàn thành khóa Fullstack.'),
            'student_three_marketing' => $this->upsertEnrollment($users['student_three'], $catalog['classes']['marketing_main'], 'completed', Carbon::now()->subDays(12), Carbon::now()->subDays(12), Carbon::now()->subDays(2), 'Đã hoàn thành khóa Marketing.'),
        ];
    }

    private function seedLearningProgress(array $users, array $catalog, array $enrollments): void
    {
        $this->upsertProgress($enrollments['student_one_fullstack'], $catalog['materials']['fullstack_video'], $users['student_one'], 100, Carbon::now()->subDays(17), Carbon::now()->subDays(16), Carbon::now()->subDays(16));
        $this->upsertProgress($enrollments['student_one_fullstack'], $catalog['materials']['fullstack_assignment'], $users['student_one'], 100, Carbon::now()->subDays(15), Carbon::now()->subDays(14), Carbon::now()->subDays(14));
        $this->upsertQuizProgress($enrollments['student_one_fullstack'], $catalog['materials']['fullstack_quiz'], $users['student_one'], Carbon::now()->subDays(13), 100, 1, 2, 2, 100, ['q1' => 'Document Object Model', 'q2' => 'click']);
        $this->upsertProgress($enrollments['student_one_fullstack'], $catalog['materials']['fullstack_laravel_video'], $users['student_one'], 100, Carbon::now()->subDays(10), Carbon::now()->subDays(9), Carbon::now()->subDays(9));

        $this->upsertProgress($enrollments['student_two_marketing'], $catalog['materials']['marketing_video'], $users['student_two'], 100, Carbon::now()->subDays(7), Carbon::now()->subDays(7), Carbon::now()->subDays(7));
        $this->upsertProgress($enrollments['student_two_marketing'], $catalog['materials']['marketing_assignment'], $users['student_two'], 75, Carbon::now()->subDays(5), Carbon::now()->subDays(4), null);

        $this->upsertProgress($enrollments['student_three_fullstack'], $catalog['materials']['fullstack_video'], $users['student_three'], 100, Carbon::now()->subDays(18), Carbon::now()->subDays(17), Carbon::now()->subDays(17));
        $this->upsertProgress($enrollments['student_three_fullstack'], $catalog['materials']['fullstack_assignment'], $users['student_three'], 100, Carbon::now()->subDays(16), Carbon::now()->subDays(15), Carbon::now()->subDays(15));
        $this->upsertQuizProgress($enrollments['student_three_fullstack'], $catalog['materials']['fullstack_quiz'], $users['student_three'], Carbon::now()->subDays(12), 100, 1, 2, 2, 100, ['q1' => 'Document Object Model', 'q2' => 'click']);
        $this->upsertProgress($enrollments['student_three_fullstack'], $catalog['materials']['fullstack_laravel_video'], $users['student_three'], 100, Carbon::now()->subDays(11), Carbon::now()->subDays(10), Carbon::now()->subDays(10));

        $this->upsertProgress($enrollments['student_three_marketing'], $catalog['materials']['marketing_video'], $users['student_three'], 100, Carbon::now()->subDays(10), Carbon::now()->subDays(9), Carbon::now()->subDays(9));
        $this->upsertProgress($enrollments['student_three_marketing'], $catalog['materials']['marketing_assignment'], $users['student_three'], 100, Carbon::now()->subDays(8), Carbon::now()->subDays(7), Carbon::now()->subDays(7));
        $this->upsertQuizProgress($enrollments['student_three_marketing'], $catalog['materials']['marketing_quiz'], $users['student_three'], Carbon::now()->subDays(6), 100, 1, 2, 2, 100, ['q1' => 'Click Through Rate', 'q2' => 'Cost Per Click']);
        $this->upsertProgress($enrollments['student_three_marketing'], $catalog['materials']['marketing_meeting'], $users['student_three'], 100, Carbon::now()->subDays(3), Carbon::now()->subDays(2), Carbon::now()->subDays(2));

        $this->upsertCertificate($catalog['courses']['fullstack'], $enrollments['student_one_fullstack'], $users['student_one'], 'KT-FS-0001', Carbon::now()->subDays(5));
        $this->upsertCertificate($catalog['courses']['fullstack'], $enrollments['student_three_fullstack'], $users['student_three'], 'KT-FS-0002', Carbon::now()->subDays(7));
        $this->upsertCertificate($catalog['courses']['marketing'], $enrollments['student_three_marketing'], $users['student_three'], 'KT-MKT-0001', Carbon::now()->subDays(2));
    }
    private function seedPayments(array $users, array $catalog): void
    {
        $this->upsertPayment('RDR-VNPAY-001', $users['student_one']->id, $catalog['classes']['fullstack_main']->id, 1990000, 'vnpay', 'completed', Carbon::now()->subDays(18), 'Thanh toán sandbox thành công qua VNPay.');
        $this->upsertPayment('RDR-BANK-001', $users['student_two']->id, $catalog['classes']['english_april']->id, 2890000, 'bank_transfer', 'pending', null, 'Học viên đã gửi yêu cầu chuyển khoản cho khóa offline, admin cần kiểm tra.');
        $this->upsertPayment('RDR-WALLET-001', $users['student_three']->id, $catalog['classes']['marketing_main']->id, 1490000, 'wallet', 'completed', Carbon::now()->subDays(12), 'Thanh toán bằng ví nội bộ.');
    }

    private function seedWallets(array $users, array $catalog): void
    {
        $walletIds = [];

        foreach ($users as $key => $user) {
            DB::table('wallets')->updateOrInsert(
                ['user_id' => $user->id],
                ['balance' => match ($key) {
                    'student_one' => 450000,
                    'student_two' => 120000,
                    'student_three' => 980000,
                    default => 0,
                }, 'created_at' => Carbon::now()->subDays(30), 'updated_at' => Carbon::now()]
            );

            $walletIds[$key] = (int) DB::table('wallets')->where('user_id', $user->id)->value('id');
        }

        DB::table('wallet_transactions')->updateOrInsert(
            ['reference' => 'RDR-DIRECT-001'],
            [
                'wallet_id' => $walletIds['student_two'],
                'course_id' => $catalog['courses']['english']->id,
                'type' => 'deposit',
                'amount' => 500000,
                'status' => 'pending',
                'metadata' => json_encode(['method' => 'direct', 'sender_name' => 'Lê Quốc Bảo', 'note' => 'Nạp để giữ chỗ khóa offline'], JSON_UNESCAPED_UNICODE),
                'expires_at' => Carbon::now()->addHours(36),
                'expired_at' => null,
                'created_at' => Carbon::now()->subHours(12),
                'updated_at' => Carbon::now()->subHours(12),
            ]
        );
    }

    private function seedPosts(array $users): void
    {
        $category = PostCategory::updateOrCreate(
            ['slug' => 'thong-bao-tuyen-sinh'],
            ['name' => 'Thông báo tuyển sinh', 'description' => 'Tin mới về khai giảng, lịch học và ưu đãi.', 'color' => '#2563eb', 'order' => 1, 'is_active' => true]
        );

        $postOne = Post::updateOrCreate(
            ['slug' => 'khai-giang-dot-thang-4'],
            [
                'title' => 'Khai giảng đợt học tháng 4 cho các khóa online và offline',
                'excerpt' => 'Cập nhật lịch khai giảng mới nhất cho đợt tháng 4 cùng nhiều suất ưu đãi học phí.',
                'content' => '<p>Trung tâm Khai Trí đã mở đăng ký cho đợt học tháng 4 với các khóa online có thể học ngay và các khóa offline cần admin duyệt trước khi vào học.</p><p>Học viên có thể xem chi tiết từng khóa học, module, đợt học và tình trạng đăng ký trực tiếp trên cổng đăng ký.</p>',
                'author_id' => $users['admin']->id,
                'category_id' => $category->id,
                'status' => 'published',
                'view_count' => 126,
                'published_at' => Carbon::now()->subDays(4),
                'meta' => ['is_featured' => true],
            ]
        );
        $postOne->forceFill(['created_at' => Carbon::now()->subDays(4), 'updated_at' => Carbon::now()->subDays(4)])->saveQuietly();

        $postTwo = Post::updateOrCreate(
            ['slug' => 'huong-dan-dang-ky-thanh-toan-vnpay'],
            [
                'title' => 'Hướng dẫn đăng ký và thanh toán khóa học qua VNPay sandbox',
                'excerpt' => 'Mô phỏng quy trình thanh toán trực tuyến ngay trên cổng đăng ký khóa học.',
                'content' => '<p>Ở môi trường demo, học viên có thể chọn thanh toán bằng VNPay sandbox để mô phỏng giao dịch trực tuyến.</p><p>Khóa online sẽ được ghi danh tự động sau thanh toán thành công, còn khóa offline sẽ chuyển sang trạng thái chờ admin duyệt.</p>',
                'author_id' => $users['admin']->id,
                'category_id' => $category->id,
                'status' => 'published',
                'view_count' => 84,
                'published_at' => Carbon::now()->subDays(1),
                'meta' => ['is_featured' => false],
            ]
        );
        $postTwo->forceFill(['created_at' => Carbon::now()->subDays(1), 'updated_at' => Carbon::now()->subDays(1)])->saveQuietly();
    }

    private function seedSettings(): void
    {
        foreach ([
            'site_name' => 'Khai Tri Edu',
            'site_tagline' => 'Cổng đăng ký khóa học trực tuyến',
            'contact_email' => 'contact@khaitri.edu.vn',
            'contact_phone' => '0867 852 853',
            'contact_address' => 'Long Xuyên, An Giang',
            'footer_text' => 'Khai Tri Edu - Cổng đăng ký khóa học trực tuyến',
            'allow_class_change' => '1',
            'class_change_deadline_days' => '3',
        ] as $key => $value) {
            Setting::set($key, $value);
        }
    }

    private function syncCourseMetrics(array $courses): void
    {
        foreach ($courses as $course) {
            $course->refresh();
            $course->syncStudyMetrics();
            $studentsCount = CourseEnrollment::query()->whereHas('courseClass', fn ($query) => $query->where('course_id', $course->id))->whereIn('status', ['approved', 'completed'])->count();
            $course->forceFill(['students_count' => $studentsCount])->saveQuietly();
        }
    }
    private function upsertUser(string $email, string $username, string $fullname, string $role): User
    {
        return User::updateOrCreate(['email' => $email], ['username' => $username, 'fullname' => $fullname, 'role' => $role, 'password' => Hash::make(self::DEMO_PASSWORD), 'is_verified' => true]);
    }

    private function upsertCategory(string $slug, string $name, string $description, string $color, int $order): CourseCategory
    {
        return CourseCategory::updateOrCreate(['slug' => $slug], ['name' => $name, 'description' => $description, 'color' => $color, 'order' => $order, 'slug' => $slug, 'is_active' => true]);
    }

    private function upsertCourse(string $slug, array $attributes): Course
    {
        $course = Course::withTrashed()->firstOrNew(['slug' => $slug]);
        $columns = collect($attributes)
            ->except(['created_at'])
            ->filter(fn ($value, $key) => Schema::hasColumn('courses', $key))
            ->all();

        $course->fill(array_merge($columns, ['slug' => $slug]));

        if (Schema::hasColumn('courses', 'deleted_at')) {
            $course->deleted_at = null;
        }

        $course->save();

        if (isset($attributes['created_at'])) {
            $course->forceFill(['created_at' => $attributes['created_at'], 'updated_at' => Carbon::now()])->saveQuietly();
        }

        return $course;
    }

    private function upsertClass(Course $course, array $attributes): CourseClass
    {
        $class = CourseClass::firstOrNew(['course_id' => $course->id, 'name' => $attributes['name']]);
        $class->fill($attributes);
        $class->course_id = $course->id;
        $class->save();
        if (isset($attributes['created_at'])) {
            $class->forceFill(['created_at' => $attributes['created_at'], 'updated_at' => Carbon::now()])->saveQuietly();
        }
        return $class;
    }

    private function upsertModule(Course $course, string $title, string $description, int $order): CourseModule
    {
        $module = CourseModule::firstOrNew(['course_id' => $course->id, 'title' => $title]);
        $module->fill(['title' => $title, 'description' => $description, 'order' => $order]);
        $module->course_id = $course->id;
        $module->save();
        return $module;
    }

    private function upsertMaterial(Course $course, CourseModule $module, string $type, string $title, string $content, int $order, int $minutes, array $metadata): CourseMaterial
    {
        $material = CourseMaterial::firstOrNew(['course_id' => $course->id, 'title' => $title]);
        $material->fill(['type' => $type, 'title' => $title, 'content' => $content, 'order' => $order, 'estimated_duration_minutes' => $minutes, 'metadata' => $metadata]);
        $material->course_id = $course->id;
        $material->course_module_id = $module->id;
        $material->save();
        return $material;
    }

    private function upsertEnrollment(User $user, CourseClass $class, string $status, ?Carbon $enrolledAt, ?Carbon $approvedAt, ?Carbon $completedAt, ?string $notes): CourseEnrollment
    {
        $enrollment = CourseEnrollment::firstOrNew(['user_id' => $user->id, 'class_id' => $class->id]);
        $enrollment->fill(['status' => $status, 'enrolled_at' => $enrolledAt, 'approved_at' => $approvedAt, 'completed_at' => $completedAt, 'notes' => $notes]);
        $enrollment->user_id = $user->id;
        $enrollment->class_id = $class->id;
        $enrollment->save();
        $enrollment->forceFill(['created_at' => $enrolledAt ?? Carbon::now(), 'updated_at' => Carbon::now()])->saveQuietly();
        return $enrollment;
    }

    private function upsertProgress(CourseEnrollment $enrollment, CourseMaterial $material, User $user, int $percent, ?Carbon $startedAt, ?Carbon $lastViewedAt, ?Carbon $completedAt): void
    {
        DB::table('course_material_progress')->updateOrInsert(['enrollment_id' => $enrollment->id, 'course_material_id' => $material->id], ['user_id' => $user->id, 'progress_percent' => $percent, 'started_at' => $startedAt, 'last_viewed_at' => $lastViewedAt, 'completed_at' => $completedAt, 'best_quiz_score' => null, 'quiz_attempts_count' => 0, 'passed_at' => null, 'meta' => json_encode(['seed' => 'render-demo']), 'created_at' => $startedAt ?? Carbon::now(), 'updated_at' => Carbon::now()]);
    }

    private function upsertQuizProgress(CourseEnrollment $enrollment, CourseMaterial $material, User $user, Carbon $completedAt, int $score, int $attemptNumber, int $totalQuestions, int $correctAnswers, int $bestScore, array $answersSummary): void
    {
        DB::table('course_material_progress')->updateOrInsert(['enrollment_id' => $enrollment->id, 'course_material_id' => $material->id], ['user_id' => $user->id, 'progress_percent' => 100, 'started_at' => $completedAt, 'last_viewed_at' => $completedAt, 'completed_at' => $completedAt, 'best_quiz_score' => $bestScore, 'quiz_attempts_count' => 1, 'passed_at' => $completedAt, 'meta' => json_encode(['seed' => 'render-demo']), 'created_at' => $completedAt, 'updated_at' => Carbon::now()]);
        DB::table('course_material_quiz_attempts')->updateOrInsert(['enrollment_id' => $enrollment->id, 'course_material_id' => $material->id, 'attempt_number' => $attemptNumber], ['user_id' => $user->id, 'total_questions' => $totalQuestions, 'correct_answers' => $correctAnswers, 'score_percent' => $score, 'is_passed' => true, 'answers_summary' => json_encode($answersSummary, JSON_UNESCAPED_UNICODE), 'completed_at' => $completedAt, 'created_at' => $completedAt, 'updated_at' => Carbon::now()]);
    }

    private function upsertCertificate(Course $course, CourseEnrollment $enrollment, User $user, string $certificateNo, Carbon $issuedAt): void
    {
        DB::table('course_certificates')->updateOrInsert(['course_id' => $course->id, 'enrollment_id' => $enrollment->id], ['user_id' => $user->id, 'certificate_no' => $certificateNo, 'issued_at' => $issuedAt, 'meta' => json_encode(['seed' => 'render-demo']), 'created_at' => $issuedAt, 'updated_at' => Carbon::now()]);
    }

    private function upsertPayment(string $reference, int $userId, int $classId, float $amount, string $method, string $status, ?Carbon $paidAt, ?string $notes): void
    {
        $payment = Payment::updateOrCreate(['reference' => $reference], ['user_id' => $userId, 'class_id' => $classId, 'amount' => $amount, 'method' => $method, 'status' => $status, 'paid_at' => $paidAt, 'notes' => $notes, 'reference' => $reference]);
        $payment->forceFill(['created_at' => $paidAt ?? Carbon::now(), 'updated_at' => Carbon::now()])->saveQuietly();
    }
}
