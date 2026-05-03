<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseCertificate;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\CourseMaterial;
use App\Models\CourseMaterialProgress;
use App\Models\CourseMaterialQuizAttempt;
use App\Models\CourseModule;
use App\Models\Payment;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Setting;
use App\Models\User;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

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

        // Mở rộng catalog cho website trông giống site thật.
        $this->call(ExpandedCatalogSeeder::class);
    }

    private function seedUsers(): array
    {
        return [
            'admin' => $this->upsertUser('admin@khaitri.edu.vn', 'admin', 'Khai Trí Admin', 'admin'),
            'instructor' => $this->upsertUser('giangvien@khaitri.edu.vn', 'giangvien', 'Nguyễn Minh An', 'instructor'),
            'student_one' => $this->upsertUser('hocvien1@khaitri.edu.vn', 'hocvien1', 'Trần Gia Hân', 'student'),
            'student_two' => $this->upsertUser('hocvien2@khaitri.edu.vn', 'hocvien2', 'Lê Quốc Bảo', 'student'),
            'student_three' => $this->upsertUser('hocvien3@khaitri.edu.vn', 'hocvien3', 'Phạm Khánh Linh', 'student'),
        ];
    }

    private function seedCatalog(array $users): array
    {
        $categories = [
            'it' => $this->upsertCategory('cong-nghe-thong-tin', 'Công nghệ thông tin', 'Lập trình và chuyển đổi số', '#1d4ed8', 1),
            'marketing' => $this->upsertCategory('marketing-kinh-doanh', 'Marketing - Kinh doanh', 'Nội dung, tăng trưởng và bán hàng hiện đại', '#f97316', 2),
            'english' => $this->upsertCategory('tieng-anh', 'Tiếng Anh', 'Ngoại ngữ phục vụ học tập và công việc', '#0f766e', 3),
        ];

        $courses = [
            'fullstack' => $this->upsertCourse('lap-trinh-web-fullstack-can-ban', [
                'title' => 'Lập trình Web Fullstack căn bản',
                'description' => 'Lộ trình từ HTML/CSS đến JavaScript và Laravel.',
                'short_description' => 'Học online theo module, có video, quiz và bài tập.',
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
                'created_at' => now()->subDays(24),
            ]),
            'marketing' => $this->upsertCourse('digital-marketing-thuc-chien-4-tuan', [
                'title' => 'Digital Marketing thực chiến 4 tuần',
                'description' => 'Tư duy nội dung, quảng cáo và phân tích hiệu quả chiến dịch.',
                'short_description' => 'Khóa online ngắn hạn cho người mới bắt đầu.',
                'category_id' => $categories['marketing']->id,
                'instructor_id' => $users['instructor']->id,
                'price' => 1890000,
                'sale_price' => 1490000,
                'level' => 'intermediate',
                'status' => 'published',
                'is_featured' => true,
                'is_popular' => false,
                'learning_type' => 'online',
                'announcement' => 'Có workshop live qua Meet mỗi tuần.',
                'students_count' => 0,
                'created_at' => now()->subDays(15),
            ]),
            'english' => $this->upsertCourse('tieng-anh-giao-tiep-b1', [
                'title' => 'Tiếng Anh giao tiếp B1',
                'description' => 'Khóa học offline tại trung tâm, có lịch học và xét duyệt hồ sơ.',
                'short_description' => 'Khóa offline có lịch học và số chỗ rõ ràng.',
                'category_id' => $categories['english']->id,
                'instructor_id' => $users['instructor']->id,
                'price' => 3200000,
                'sale_price' => 2890000,
                'level' => 'intermediate',
                'status' => 'published',
                'is_featured' => false,
                'is_popular' => true,
                'learning_type' => 'offline',
                'announcement' => 'Khóa offline cần admin duyệt hồ sơ trước khi vào học.',
                'students_count' => 0,
                'created_at' => now()->subDays(7),
            ]),
        ];

        $classes = [
            'fullstack_main' => $this->upsertClass($courses['fullstack'], [
                'name' => 'Học ngay - Kỳ tháng 04',
                'instructor_id' => $users['instructor']->id,
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->addDays(50)->toDateString(),
                'schedule' => 'Tự học linh hoạt trên hệ thống',
                'meeting_info' => 'Workshop thứ 7 lúc 20:00',
                'max_students' => 0,
                'price_override' => null,
                'status' => 'active',
                'created_at' => now()->subDays(10),
            ]),
            'marketing_main' => $this->upsertClass($courses['marketing'], [
                'name' => 'Học ngay - Kỳ tháng 04',
                'instructor_id' => $users['instructor']->id,
                'start_date' => now()->subDays(6)->toDateString(),
                'end_date' => now()->addDays(28)->toDateString(),
                'schedule' => 'Tự học + workshop live tối thứ 5',
                'meeting_info' => 'Google Meet lúc 19:30 thứ 5',
                'max_students' => 0,
                'price_override' => null,
                'status' => 'active',
                'created_at' => now()->subDays(6),
            ]),
            'english_april' => $this->upsertClass($courses['english'], [
                'name' => 'Khóa 01 - Tháng 04',
                'instructor_id' => $users['instructor']->id,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(65)->toDateString(),
                'schedule' => 'Thứ 2, 4, 6 | 18:30 - 20:00',
                'meeting_info' => 'Phòng B203 - Cơ sở Long Xuyên',
                'max_students' => 18,
                'price_override' => null,
                'status' => 'active',
                'created_at' => now()->subDays(4),
            ]),
            'english_may' => $this->upsertClass($courses['english'], [
                'name' => 'Khóa 02 - Tháng 05',
                'instructor_id' => $users['instructor']->id,
                'start_date' => now()->addDays(28)->toDateString(),
                'end_date' => now()->addDays(88)->toDateString(),
                'schedule' => 'Thứ 3, 5, 7 | 18:30 - 20:00',
                'meeting_info' => 'Phòng A105 - Cơ sở Long Xuyên',
                'max_students' => 20,
                'price_override' => null,
                'status' => 'active',
                'created_at' => now()->subDays(2),
            ]),
        ];

        $modules = [
            'fullstack_html' => $this->upsertModule($courses['fullstack'], 'HTML và CSS nền tảng', 'Dựng giao diện cơ bản.', 1),
            'fullstack_js' => $this->upsertModule($courses['fullstack'], 'JavaScript thực chiến', 'DOM, event và bài toán frontend.', 2),
            'fullstack_laravel' => $this->upsertModule($courses['fullstack'], 'Laravel căn bản', 'Route, controller, blade và xử lý request.', 3),
            'marketing_content' => $this->upsertModule($courses['marketing'], 'Content Strategy', 'Lập kế hoạch nội dung theo tuần.', 1),
            'marketing_ads' => $this->upsertModule($courses['marketing'], 'Ads and Performance', 'Đọc chỉ số và tối ưu chiến dịch.', 2),
            'english_listening' => $this->upsertModule($courses['english'], 'Nghe', 'Luyện nghe tình huống giao tiếp.', 1),
            'english_speaking' => $this->upsertModule($courses['english'], 'Nói', 'Tăng phản xạ giao tiếp.', 2),
        ];

        $materials = [
            'fullstack_video' => $this->upsertMaterial($courses['fullstack'], $modules['fullstack_html'], 'video', 'Khởi động với HTML và CSS', 'Video định hướng lộ trình.', 1, 25, ['url' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U']),
            'fullstack_assignment' => $this->upsertMaterial($courses['fullstack'], $modules['fullstack_html'], 'assignment', 'Bài tập landing page cá nhân', 'Dựng landing page responsive cơ bản.', 2, 20, ['content' => 'Hoàn thiện landing page với 3 section.']),
            'fullstack_quiz' => $this->upsertMaterial($courses['fullstack'], $modules['fullstack_js'], 'quiz', 'Quiz JavaScript căn bản', 'Ôn tập DOM và event.', 3, 15, ['passing_score' => 70, 'questions' => [['question' => 'DOM viết tắt của gì?', 'answer' => 'Document Object Model'], ['question' => 'Sự kiện bấm nút là gì?', 'answer' => 'click']]]),
            'fullstack_laravel_video' => $this->upsertMaterial($courses['fullstack'], $modules['fullstack_laravel'], 'video', 'Laravel route controller blade', 'Tổng quan xử lý request.', 4, 35, ['url' => 'https://www.youtube.com/watch?v=ImtZ5yENzgE']),
            'marketing_video' => $this->upsertMaterial($courses['marketing'], $modules['marketing_content'], 'video', 'Tư duy content chuyển đổi', 'Xác định insight, hook và CTA.', 1, 22, ['url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw']),
            'marketing_assignment' => $this->upsertMaterial($courses['marketing'], $modules['marketing_content'], 'assignment', 'Lập kế hoạch nội dung 7 ngày', 'Thiết kế lịch nội dung ngắn hạn.', 2, 18, ['content' => 'Xây dựng lịch nội dung 7 ngày cho fanpage.']),
            'marketing_quiz' => $this->upsertMaterial($courses['marketing'], $modules['marketing_ads'], 'quiz', 'Quiz chỉ số quảng cáo', 'Ôn tập CTR, CPC và conversion rate.', 3, 12, ['passing_score' => 75, 'questions' => [['question' => 'CTR viết tắt của gì?', 'answer' => 'Click Through Rate'], ['question' => 'CPC là gì?', 'answer' => 'Cost Per Click']]]),
            'marketing_meeting' => $this->upsertMaterial($courses['marketing'], $modules['marketing_ads'], 'meeting', 'Workshop tối ưu chiến dịch', 'Buổi live phân tích case thực tế.', 4, 90, ['meeting_url' => 'https://meet.google.com/abc-demo-khai-tri', 'meeting_starts_at' => now()->subDays(2)->setTime(19, 30)->toDateTimeString(), 'meeting_ends_at' => now()->subDays(2)->setTime(21, 0)->toDateTimeString()]),
            'english_listening_video' => $this->upsertMaterial($courses['english'], $modules['english_listening'], 'video', 'Nghe giao tiếp cơ bản', 'Tình huống nghe giao tiếp thực tế.', 1, 30, ['url' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U']),
            'english_speaking_assignment' => $this->upsertMaterial($courses['english'], $modules['english_speaking'], 'assignment', 'Thực hành speaking theo cặp', 'Tập nói theo tình huống có sẵn.', 2, 25, ['content' => 'Ghi âm và nộp bài thực hành.']),
        ];

        return [
            'courses' => $courses,
            'classes' => $classes,
            'modules' => $modules,
            'materials' => $materials,
        ];
    }

    private function seedEnrollments(array $users, array $catalog): array
    {
        return [
            'student_one_fullstack' => $this->upsertEnrollment($users['student_one'], $catalog['classes']['fullstack_main'], 'completed', now()->subDays(18), now()->subDays(18), now()->subDays(5), 'Hoàn thành khóa học và đủ điều kiện cấp chứng chỉ.'),
            'student_two_marketing' => $this->upsertEnrollment($users['student_two'], $catalog['classes']['marketing_main'], 'approved', now()->subDays(8), now()->subDays(8), null, 'Đang học giữa kỳ và đã mở quyền học ngay.'),
            'student_two_english_pending' => $this->upsertEnrollment($users['student_two'], $catalog['classes']['english_april'], 'pending', now()->subDays(1), null, null, 'Chờ admin duyệt hồ sơ cho đợt học offline.'),
            'student_three_fullstack' => $this->upsertEnrollment($users['student_three'], $catalog['classes']['fullstack_main'], 'completed', now()->subDays(20), now()->subDays(20), now()->subDays(7), 'Đã hoàn thành khóa Fullstack.'),
            'student_three_marketing' => $this->upsertEnrollment($users['student_three'], $catalog['classes']['marketing_main'], 'completed', now()->subDays(12), now()->subDays(12), now()->subDays(2), 'Đã hoàn thành khóa Marketing.'),
        ];
    }

    private function seedLearningProgress(array $users, array $catalog, array $enrollments): void
    {
        $this->upsertProgress($enrollments['student_one_fullstack'], $catalog['materials']['fullstack_video'], $users['student_one'], 100, now()->subDays(17), now()->subDays(16), now()->subDays(16));
        $this->upsertProgress($enrollments['student_one_fullstack'], $catalog['materials']['fullstack_assignment'], $users['student_one'], 100, now()->subDays(15), now()->subDays(14), now()->subDays(14));
        $this->upsertQuizProgress($enrollments['student_one_fullstack'], $catalog['materials']['fullstack_quiz'], $users['student_one'], now()->subDays(13), 100, 1, 2, 2, 100, ['q1' => 'Document Object Model', 'q2' => 'click']);
        $this->upsertProgress($enrollments['student_one_fullstack'], $catalog['materials']['fullstack_laravel_video'], $users['student_one'], 100, now()->subDays(10), now()->subDays(9), now()->subDays(9));

        $this->upsertProgress($enrollments['student_two_marketing'], $catalog['materials']['marketing_video'], $users['student_two'], 100, now()->subDays(7), now()->subDays(7), now()->subDays(7));
        $this->upsertProgress($enrollments['student_two_marketing'], $catalog['materials']['marketing_assignment'], $users['student_two'], 75, now()->subDays(5), now()->subDays(4), null);

        $this->upsertProgress($enrollments['student_three_fullstack'], $catalog['materials']['fullstack_video'], $users['student_three'], 100, now()->subDays(18), now()->subDays(17), now()->subDays(17));
        $this->upsertProgress($enrollments['student_three_fullstack'], $catalog['materials']['fullstack_assignment'], $users['student_three'], 100, now()->subDays(16), now()->subDays(15), now()->subDays(15));
        $this->upsertQuizProgress($enrollments['student_three_fullstack'], $catalog['materials']['fullstack_quiz'], $users['student_three'], now()->subDays(12), 100, 1, 2, 2, 100, ['q1' => 'Document Object Model', 'q2' => 'click']);
        $this->upsertProgress($enrollments['student_three_fullstack'], $catalog['materials']['fullstack_laravel_video'], $users['student_three'], 100, now()->subDays(11), now()->subDays(10), now()->subDays(10));

        $this->upsertProgress($enrollments['student_three_marketing'], $catalog['materials']['marketing_video'], $users['student_three'], 100, now()->subDays(10), now()->subDays(9), now()->subDays(9));
        $this->upsertProgress($enrollments['student_three_marketing'], $catalog['materials']['marketing_assignment'], $users['student_three'], 100, now()->subDays(8), now()->subDays(7), now()->subDays(7));
        $this->upsertQuizProgress($enrollments['student_three_marketing'], $catalog['materials']['marketing_quiz'], $users['student_three'], now()->subDays(6), 100, 1, 2, 2, 100, ['q1' => 'Click Through Rate', 'q2' => 'Cost Per Click']);
        $this->upsertProgress($enrollments['student_three_marketing'], $catalog['materials']['marketing_meeting'], $users['student_three'], 100, now()->subDays(3), now()->subDays(2), now()->subDays(2));

        $this->upsertCertificate($catalog['courses']['fullstack'], $enrollments['student_one_fullstack'], $users['student_one'], 'KT-FS-0001', now()->subDays(5));
        $this->upsertCertificate($catalog['courses']['fullstack'], $enrollments['student_three_fullstack'], $users['student_three'], 'KT-FS-0002', now()->subDays(7));
        $this->upsertCertificate($catalog['courses']['marketing'], $enrollments['student_three_marketing'], $users['student_three'], 'KT-MKT-0001', now()->subDays(2));
    }

    private function seedPayments(array $users, array $catalog): void
    {
        $this->upsertPayment('RDR-VNPAY-001', $users['student_one']->id, $catalog['classes']['fullstack_main'], 1990000, 'vnpay', 'completed', now()->subDays(18), 'Thanh toán sandbox thành công qua VNPay.');
        $this->upsertPayment('RDR-BANK-001', $users['student_two']->id, $catalog['classes']['english_april'], 2890000, 'bank_transfer', 'pending', null, 'Học viên đã gửi yêu cầu chuyển khoản cho khóa offline.');
        $this->upsertPayment('RDR-WALLET-001', $users['student_three']->id, $catalog['classes']['marketing_main'], 1490000, 'wallet', 'completed', now()->subDays(12), 'Thanh toán bằng ví nội bộ.');
    }

    private function seedWallets(array $users, array $catalog): void
    {
        foreach ($users as $key => $user) {
            $wallet = Wallet::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'balance' => match ($key) {
                        'student_one' => 450000,
                        'student_two' => 120000,
                        'student_three' => 980000,
                        default => 0,
                    },
                ]
            );

            $wallet->forceFill([
                'created_at' => now()->subDays(30),
                'updated_at' => now(),
            ])->saveQuietly();
        }

        $topup = WalletTransaction::updateOrCreate(
            ['reference' => 'RDR-DIRECT-001'],
            [
                'wallet_id' => $users['student_two']->wallet()->value('id'),
                'course_id' => $catalog['courses']['english']->id,
                'type' => 'deposit',
                'amount' => 500000,
                'status' => 'pending',
                'metadata' => [
                    'method' => 'direct',
                    'sender_name' => 'Lê Quốc Bảo',
                    'note' => 'Nạp để giữ chỗ khóa offline',
                ],
                'expires_at' => now()->addHours(36),
                'expired_at' => null,
            ]
        );

        $topup->forceFill([
            'created_at' => now()->subHours(12),
            'updated_at' => now()->subHours(12),
        ])->saveQuietly();
    }

    private function seedPosts(array $users): void
    {
        $category = PostCategory::updateOrCreate(
            ['slug' => 'thong-bao-tuyen-sinh'],
            [
                'name' => 'Thông báo tuyển sinh',
                'description' => 'Tin mới về khai giảng, lịch học và ưu đãi.',
                'color' => '#2563eb',
                'order' => 1,
                'is_active' => true,
            ]
        );

        $postOne = Post::updateOrCreate(
            ['slug' => 'khai-giang-dot-thang-4'],
            [
                'title' => 'Khai giảng đợt học tháng 4 cho các khóa online và offline',
                'excerpt' => 'Cập nhật lịch khai giảng mới nhất cho đợt tháng 4 cùng nhiều ưu đãi học phí.',
                'content' => '<p>Trung tâm Khai Trí mở đăng ký cho đợt học tháng 4 với các khóa online có thể học ngay và các khóa offline cần admin duyệt.</p>',
                'author_id' => $users['admin']->id,
                'category_id' => $category->id,
                'status' => 'published',
                'view_count' => 126,
                'published_at' => now()->subDays(4),
                'meta' => ['is_featured' => true],
            ]
        );

        $postOne->forceFill([
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ])->saveQuietly();

        $postTwo = Post::updateOrCreate(
            ['slug' => 'huong-dan-dang-ky-thanh-toan-vnpay'],
            [
                'title' => 'Hướng dẫn đăng ký và thanh toán khóa học qua VNPay sandbox',
                'excerpt' => 'Mô phỏng quy trình thanh toán trực tuyến ngay trên cổng đăng ký khóa học.',
                'content' => '<p>Môi trường demo cho phép học viên thanh toán bằng VNPay sandbox để mô phỏng giao dịch trực tuyến.</p>',
                'author_id' => $users['admin']->id,
                'category_id' => $category->id,
                'status' => 'published',
                'view_count' => 84,
                'published_at' => now()->subDays(1),
                'meta' => ['is_featured' => false],
            ]
        );

        $postTwo->forceFill([
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ])->saveQuietly();
    }

    private function seedSettings(): void
    {
        foreach ([
            'site_name' => 'Khai Trí Edu',
            'site_tagline' => 'Cổng đăng ký khóa học trực tuyến',
            'contact_email' => 'contact@khaitri.edu.vn',
            'contact_phone' => '0867 852 853',
            'contact_address' => 'Long Xuyên, An Giang',
            'footer_text' => 'Khai Trí Edu - Cổng đăng ký khóa học trực tuyến',
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

            $studentsCount = CourseEnrollment::query()
                ->where('course_id', $course->id)
                ->whereIn('status', ['approved', 'completed'])
                ->count();

            $course->forceFill(['students_count' => $studentsCount])->saveQuietly();
        }
    }

    private function upsertUser(string $email, string $username, string $fullname, string $role): User
    {
        return User::updateOrCreate(
            ['email' => $email],
            [
                'username' => $username,
                'fullname' => $fullname,
                'role' => $role,
                'password' => Hash::make(self::DEMO_PASSWORD),
                'is_verified' => true,
            ]
        );
    }

    private function upsertCategory(string $slug, string $name, string $description, string $color, int $order): CourseCategory
    {
        return CourseCategory::updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'description' => $description,
                'color' => $color,
                'order' => $order,
                'is_active' => true,
            ]
        );
    }

    private function upsertCourse(string $slug, array $attributes): Course
    {
        $course = Course::withTrashed()->firstOrNew(['slug' => $slug]);
        $course->fill(array_merge(collect($attributes)->except(['created_at'])->all(), ['slug' => $slug]));
        $course->deleted_at = null;
        $course->save();

        if (isset($attributes['created_at'])) {
            $course->forceFill([
                'created_at' => $attributes['created_at'],
                'updated_at' => now(),
            ])->saveQuietly();
        }

        return $course;
    }

    private function upsertClass(Course $course, array $attributes): CourseClass
    {
        $class = CourseClass::firstOrNew([
            'course_id' => $course->id,
            'name' => $attributes['name'],
        ]);

        $class->fill($this->withoutNullDecimalAttributes($attributes, ['price_override']));
        $class->course_id = $course->id;
        $class->save();

        if (isset($attributes['created_at'])) {
            $class->forceFill([
                'created_at' => $attributes['created_at'],
                'updated_at' => now(),
            ])->saveQuietly();
        }

        return $class;
    }

    private function upsertModule(Course $course, string $title, string $description, int $order): CourseModule
    {
        $module = CourseModule::firstOrNew([
            'course_id' => $course->id,
            'title' => $title,
        ]);

        $module->fill([
            'title' => $title,
            'description' => $description,
            'order' => $order,
        ]);

        $module->course_id = $course->id;
        $module->save();

        return $module;
    }

    private function upsertMaterial(Course $course, CourseModule $module, string $type, string $title, string $content, int $order, int $minutes, array $metadata): CourseMaterial
    {
        $material = CourseMaterial::firstOrNew([
            'course_id' => $course->id,
            'title' => $title,
        ]);

        $material->fill([
            'type' => $type,
            'title' => $title,
            'content' => $content,
            'order' => $order,
            'estimated_duration_minutes' => $minutes,
            'metadata' => $metadata,
        ]);

        $material->course_id = $course->id;
        $material->course_module_id = $module->id;
        $material->save();

        return $material;
    }

    private function upsertEnrollment(User $user, CourseClass $class, string $status, ?Carbon $enrolledAt, ?Carbon $approvedAt, ?Carbon $completedAt, ?string $notes): CourseEnrollment
    {
        $enrollment = CourseEnrollment::firstOrNew([
            'user_id' => $user->id,
            'class_id' => $class->id,
        ]);

        $enrollment->fill([
            'course_id' => $class->course_id,
            'status' => $status,
            'enrolled_at' => $enrolledAt,
            'approved_at' => $approvedAt,
            'completed_at' => $completedAt,
            'notes' => $notes,
        ]);

        $enrollment->user_id = $user->id;
        $enrollment->class_id = $class->id;
        $enrollment->save();

        $enrollment->forceFill([
            'created_at' => $enrolledAt ?? now(),
            'updated_at' => now(),
        ])->saveQuietly();

        return $enrollment;
    }

    private function upsertProgress(CourseEnrollment $enrollment, CourseMaterial $material, User $user, int $percent, ?Carbon $startedAt, ?Carbon $lastViewedAt, ?Carbon $completedAt): void
    {
        $progress = CourseMaterialProgress::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'course_material_id' => $material->id,
            ],
            [
                'user_id' => $user->id,
                'progress_percent' => $percent,
                'started_at' => $startedAt,
                'last_viewed_at' => $lastViewedAt,
                'completed_at' => $completedAt,
                'quiz_attempts_count' => 0,
                'passed_at' => null,
                'meta' => ['seed' => 'render-demo'],
            ],
        );

        $progress->forceFill([
            'created_at' => $startedAt ?? now(),
            'updated_at' => now(),
        ])->saveQuietly();
    }

    private function upsertQuizProgress(CourseEnrollment $enrollment, CourseMaterial $material, User $user, Carbon $completedAt, int $score, int $attemptNumber, int $totalQuestions, int $correctAnswers, int $bestScore, array $answersSummary): void
    {
        $progress = CourseMaterialProgress::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'course_material_id' => $material->id,
            ],
            [
                'user_id' => $user->id,
                'progress_percent' => 100,
                'started_at' => $completedAt,
                'last_viewed_at' => $completedAt,
                'completed_at' => $completedAt,
                'best_quiz_score' => $bestScore,
                'quiz_attempts_count' => 1,
                'passed_at' => $completedAt,
                'meta' => ['seed' => 'render-demo'],
            ]
        );

        $progress->forceFill([
            'created_at' => $completedAt,
            'updated_at' => now(),
        ])->saveQuietly();

        $attempt = CourseMaterialQuizAttempt::updateOrCreate(
            [
                'enrollment_id' => $enrollment->id,
                'course_material_id' => $material->id,
                'attempt_number' => $attemptNumber,
            ],
            [
                'user_id' => $user->id,
                'total_questions' => $totalQuestions,
                'correct_answers' => $correctAnswers,
                'score_percent' => $score,
                'is_passed' => true,
                'answers_summary' => $answersSummary,
                'completed_at' => $completedAt,
            ]
        );

        $attempt->forceFill([
            'created_at' => $completedAt,
            'updated_at' => now(),
        ])->saveQuietly();
    }

    private function upsertCertificate(Course $course, CourseEnrollment $enrollment, User $user, string $certificateNo, Carbon $issuedAt): void
    {
        $certificate = CourseCertificate::updateOrCreate(
            [
                'course_id' => $course->id,
                'enrollment_id' => $enrollment->id,
            ],
            [
                'user_id' => $user->id,
                'certificate_no' => $certificateNo,
                'issued_at' => $issuedAt,
                'meta' => ['seed' => 'render-demo'],
            ]
        );

        $certificate->forceFill([
            'created_at' => $issuedAt,
            'updated_at' => now(),
        ])->saveQuietly();
    }

    private function upsertPayment(string $reference, int $userId, CourseClass $class, float $amount, string $method, string $status, ?Carbon $paidAt, ?string $notes): void
    {
        $payment = Payment::updateOrCreate(
            ['reference' => $reference],
            [
                'user_id' => $userId,
                'course_id' => $class->course_id,
                'class_id' => $class->id,
                'amount' => $amount,
                'base_amount' => $amount,
                'discount_amount' => 0,
                'method' => $method,
                'status' => $status,
                'paid_at' => $paidAt,
                'notes' => $notes,
                'reference' => $reference,
                'metadata' => ['seed' => 'render-demo'],
            ]
        );

        $payment->forceFill([
            'created_at' => $paidAt ?? now(),
            'updated_at' => now(),
        ])->saveQuietly();
    }

    private function withoutNullDecimalAttributes(array $attributes, array $decimalKeys): array
    {
        foreach ($decimalKeys as $key) {
            if (array_key_exists($key, $attributes) && ($attributes[$key] === null || $attributes[$key] === '')) {
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }
}
