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
    }

    private function seedUsers(): array
    {
        return [
            'admin' => $this->upsertUser('admin@khaitri.edu.vn', 'admin', 'Khai Tri Admin', 'admin'),
            'instructor' => $this->upsertUser('giangvien@khaitri.edu.vn', 'giangvien', 'Nguyen Minh An', 'instructor'),
            'student_one' => $this->upsertUser('hocvien1@khaitri.edu.vn', 'hocvien1', 'Tran Gia Han', 'student'),
            'student_two' => $this->upsertUser('hocvien2@khaitri.edu.vn', 'hocvien2', 'Le Quoc Bao', 'student'),
            'student_three' => $this->upsertUser('hocvien3@khaitri.edu.vn', 'hocvien3', 'Pham Khanh Linh', 'student'),
        ];
    }

    private function seedCatalog(array $users): array
    {
        $categories = [
            'it' => $this->upsertCategory('cong-nghe-thong-tin', 'Cong nghe thong tin', 'Lap trinh va chuyen doi so', '#1d4ed8', 1),
            'marketing' => $this->upsertCategory('marketing-kinh-doanh', 'Marketing - Kinh doanh', 'Noi dung, tang truong va ban hang hien dai', '#f97316', 2),
            'english' => $this->upsertCategory('tieng-anh', 'Tieng Anh', 'Ngoai ngu phuc vu hoc tap va cong viec', '#0f766e', 3),
        ];

        $courses = [
            'fullstack' => $this->upsertCourse('lap-trinh-web-fullstack-can-ban', [
                'title' => 'Lap trinh Web Fullstack can ban',
                'description' => 'Lo trinh tu HTML/CSS den JavaScript va Laravel.',
                'short_description' => 'Hoc online theo module, co video, quiz va bai tap.',
                'category_id' => $categories['it']->id,
                'instructor_id' => $users['instructor']->id,
                'price' => 2490000,
                'sale_price' => 1990000,
                'level' => 'beginner',
                'status' => 'published',
                'is_featured' => true,
                'is_popular' => true,
                'learning_type' => 'online',
                'announcement' => 'Dang ky xong co the vao hoc ngay tren he thong.',
                'students_count' => 0,
                'created_at' => now()->subDays(24),
            ]),
            'marketing' => $this->upsertCourse('digital-marketing-thuc-chien-4-tuan', [
                'title' => 'Digital Marketing thuc chien 4 tuan',
                'description' => 'Tu duy noi dung, quang cao va phan tich hieu qua chien dich.',
                'short_description' => 'Khoa online ngan han cho nguoi moi bat dau.',
                'category_id' => $categories['marketing']->id,
                'instructor_id' => $users['instructor']->id,
                'price' => 1890000,
                'sale_price' => 1490000,
                'level' => 'intermediate',
                'status' => 'published',
                'is_featured' => true,
                'is_popular' => false,
                'learning_type' => 'online',
                'announcement' => 'Co workshop live qua Meet moi tuan.',
                'students_count' => 0,
                'created_at' => now()->subDays(15),
            ]),
            'english' => $this->upsertCourse('tieng-anh-giao-tiep-b1', [
                'title' => 'Tieng Anh giao tiep B1',
                'description' => 'Khoa hoc offline tai trung tam, co lich hoc va xet duyet ho so.',
                'short_description' => 'Khoa offline co lich hoc va so cho ro rang.',
                'category_id' => $categories['english']->id,
                'instructor_id' => $users['instructor']->id,
                'price' => 3200000,
                'sale_price' => 2890000,
                'level' => 'intermediate',
                'status' => 'published',
                'is_featured' => false,
                'is_popular' => true,
                'learning_type' => 'offline',
                'announcement' => 'Khoa offline can admin duyet ho so truoc khi vao hoc.',
                'students_count' => 0,
                'created_at' => now()->subDays(7),
            ]),
        ];

        $classes = [
            'fullstack_main' => $this->upsertClass($courses['fullstack'], [
                'name' => 'Hoc ngay - Ky thang 04',
                'instructor_id' => $users['instructor']->id,
                'start_date' => now()->subDays(10)->toDateString(),
                'end_date' => now()->addDays(50)->toDateString(),
                'schedule' => 'Tu hoc linh hoat tren he thong',
                'meeting_info' => 'Workshop thu 7 luc 20:00',
                'max_students' => 0,
                'price_override' => null,
                'status' => 'active',
                'created_at' => now()->subDays(10),
            ]),
            'marketing_main' => $this->upsertClass($courses['marketing'], [
                'name' => 'Hoc ngay - Ky thang 04',
                'instructor_id' => $users['instructor']->id,
                'start_date' => now()->subDays(6)->toDateString(),
                'end_date' => now()->addDays(28)->toDateString(),
                'schedule' => 'Tu hoc + workshop live toi thu 5',
                'meeting_info' => 'Google Meet luc 19:30 thu 5',
                'max_students' => 0,
                'price_override' => null,
                'status' => 'active',
                'created_at' => now()->subDays(6),
            ]),
            'english_april' => $this->upsertClass($courses['english'], [
                'name' => 'Khoa 01 - Thang 04',
                'instructor_id' => $users['instructor']->id,
                'start_date' => now()->addDays(5)->toDateString(),
                'end_date' => now()->addDays(65)->toDateString(),
                'schedule' => 'Thu 2, 4, 6 | 18:30 - 20:00',
                'meeting_info' => 'Phong B203 - Co so Long Xuyen',
                'max_students' => 18,
                'price_override' => null,
                'status' => 'active',
                'created_at' => now()->subDays(4),
            ]),
            'english_may' => $this->upsertClass($courses['english'], [
                'name' => 'Khoa 02 - Thang 05',
                'instructor_id' => $users['instructor']->id,
                'start_date' => now()->addDays(28)->toDateString(),
                'end_date' => now()->addDays(88)->toDateString(),
                'schedule' => 'Thu 3, 5, 7 | 18:30 - 20:00',
                'meeting_info' => 'Phong A105 - Co so Long Xuyen',
                'max_students' => 20,
                'price_override' => null,
                'status' => 'active',
                'created_at' => now()->subDays(2),
            ]),
        ];

        $modules = [
            'fullstack_html' => $this->upsertModule($courses['fullstack'], 'HTML va CSS nen tang', 'Dung giao dien co ban.', 1),
            'fullstack_js' => $this->upsertModule($courses['fullstack'], 'JavaScript thuc chien', 'DOM, event va bai toan frontend.', 2),
            'fullstack_laravel' => $this->upsertModule($courses['fullstack'], 'Laravel can ban', 'Route, controller, blade va xu ly request.', 3),
            'marketing_content' => $this->upsertModule($courses['marketing'], 'Content Strategy', 'Lap ke hoach noi dung theo tuan.', 1),
            'marketing_ads' => $this->upsertModule($courses['marketing'], 'Ads and Performance', 'Doc chi so va toi uu chien dich.', 2),
            'english_listening' => $this->upsertModule($courses['english'], 'Nghe', 'Luyen nghe tinh huong giao tiep.', 1),
            'english_speaking' => $this->upsertModule($courses['english'], 'Noi', 'Tang phan xa giao tiep.', 2),
        ];

        $materials = [
            'fullstack_video' => $this->upsertMaterial($courses['fullstack'], $modules['fullstack_html'], 'video', 'Khoi dong voi HTML va CSS', 'Video dinh huong lo trinh.', 1, 25, ['url' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U']),
            'fullstack_assignment' => $this->upsertMaterial($courses['fullstack'], $modules['fullstack_html'], 'assignment', 'Bai tap landing page ca nhan', 'Dung landing page responsive co ban.', 2, 20, ['content' => 'Hoan thien landing page voi 3 section.']),
            'fullstack_quiz' => $this->upsertMaterial($courses['fullstack'], $modules['fullstack_js'], 'quiz', 'Quiz JavaScript can ban', 'On tap DOM va event.', 3, 15, ['passing_score' => 70, 'questions' => [['question' => 'DOM viet tat cua gi?', 'answer' => 'Document Object Model'], ['question' => 'Su kien bam nut la gi?', 'answer' => 'click']]]),
            'fullstack_laravel_video' => $this->upsertMaterial($courses['fullstack'], $modules['fullstack_laravel'], 'video', 'Laravel route controller blade', 'Tong quan xu ly request.', 4, 35, ['url' => 'https://www.youtube.com/watch?v=ImtZ5yENzgE']),
            'marketing_video' => $this->upsertMaterial($courses['marketing'], $modules['marketing_content'], 'video', 'Tu duy content chuyen doi', 'Xac dinh insight, hook va CTA.', 1, 22, ['url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw']),
            'marketing_assignment' => $this->upsertMaterial($courses['marketing'], $modules['marketing_content'], 'assignment', 'Lap ke hoach noi dung 7 ngay', 'Thiet ke lich noi dung ngan han.', 2, 18, ['content' => 'Xay dung lich noi dung 7 ngay cho fanpage.']),
            'marketing_quiz' => $this->upsertMaterial($courses['marketing'], $modules['marketing_ads'], 'quiz', 'Quiz chi so quang cao', 'On tap CTR, CPC va conversion rate.', 3, 12, ['passing_score' => 75, 'questions' => [['question' => 'CTR viet tat cua gi?', 'answer' => 'Click Through Rate'], ['question' => 'CPC la gi?', 'answer' => 'Cost Per Click']]]),
            'marketing_meeting' => $this->upsertMaterial($courses['marketing'], $modules['marketing_ads'], 'meeting', 'Workshop toi uu chien dich', 'Buoi live phan tich case thuc te.', 4, 90, ['meeting_url' => 'https://meet.google.com/abc-demo-khai-tri', 'meeting_starts_at' => now()->subDays(2)->setTime(19, 30)->toDateTimeString(), 'meeting_ends_at' => now()->subDays(2)->setTime(21, 0)->toDateTimeString()]),
            'english_listening_video' => $this->upsertMaterial($courses['english'], $modules['english_listening'], 'video', 'Nghe giao tiep co ban', 'Tinh huong nghe giao tiep thuc te.', 1, 30, ['url' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U']),
            'english_speaking_assignment' => $this->upsertMaterial($courses['english'], $modules['english_speaking'], 'assignment', 'Thuc hanh speaking theo cap', 'Tap noi theo tinh huong co san.', 2, 25, ['content' => 'Ghi am va nop bai thuc hanh.']),
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
            'student_one_fullstack' => $this->upsertEnrollment($users['student_one'], $catalog['classes']['fullstack_main'], 'completed', now()->subDays(18), now()->subDays(18), now()->subDays(5), 'Hoan thanh khoa hoc va du dieu kien cap chung chi.'),
            'student_two_marketing' => $this->upsertEnrollment($users['student_two'], $catalog['classes']['marketing_main'], 'approved', now()->subDays(8), now()->subDays(8), null, 'Dang hoc giua ky va da mo quyen hoc ngay.'),
            'student_two_english_pending' => $this->upsertEnrollment($users['student_two'], $catalog['classes']['english_april'], 'pending', now()->subDays(1), null, null, 'Cho admin duyet ho so cho dot hoc offline.'),
            'student_three_fullstack' => $this->upsertEnrollment($users['student_three'], $catalog['classes']['fullstack_main'], 'completed', now()->subDays(20), now()->subDays(20), now()->subDays(7), 'Da hoan thanh khoa Fullstack.'),
            'student_three_marketing' => $this->upsertEnrollment($users['student_three'], $catalog['classes']['marketing_main'], 'completed', now()->subDays(12), now()->subDays(12), now()->subDays(2), 'Da hoan thanh khoa Marketing.'),
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
        $this->upsertPayment('RDR-VNPAY-001', $users['student_one']->id, $catalog['classes']['fullstack_main'], 1990000, 'vnpay', 'completed', now()->subDays(18), 'Thanh toan sandbox thanh cong qua VNPay.');
        $this->upsertPayment('RDR-BANK-001', $users['student_two']->id, $catalog['classes']['english_april'], 2890000, 'bank_transfer', 'pending', null, 'Hoc vien da gui yeu cau chuyen khoan cho khoa offline.');
        $this->upsertPayment('RDR-WALLET-001', $users['student_three']->id, $catalog['classes']['marketing_main'], 1490000, 'wallet', 'completed', now()->subDays(12), 'Thanh toan bang vi noi bo.');
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
                    'sender_name' => 'Le Quoc Bao',
                    'note' => 'Nap de giu cho khoa offline',
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
                'name' => 'Thong bao tuyen sinh',
                'description' => 'Tin moi ve khai giang, lich hoc va uu dai.',
                'color' => '#2563eb',
                'order' => 1,
                'is_active' => true,
            ]
        );

        $postOne = Post::updateOrCreate(
            ['slug' => 'khai-giang-dot-thang-4'],
            [
                'title' => 'Khai giang dot hoc thang 4 cho cac khoa online va offline',
                'excerpt' => 'Cap nhat lich khai giang moi nhat cho dot thang 4 cung nhieu uu dai hoc phi.',
                'content' => '<p>Trung tam Khai Tri mo dang ky cho dot hoc thang 4 voi cac khoa online co the hoc ngay va cac khoa offline can admin duyet.</p>',
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
                'title' => 'Huong dan dang ky va thanh toan khoa hoc qua VNPay sandbox',
                'excerpt' => 'Mo phong quy trinh thanh toan truc tuyen ngay tren cong dang ky khoa hoc.',
                'content' => '<p>Moi truong demo cho phep hoc vien thanh toan bang VNPay sandbox de mo phong giao dich truc tuyen.</p>',
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
            'site_name' => 'Khai Tri Edu',
            'site_tagline' => 'Cong dang ky khoa hoc truc tuyen',
            'contact_email' => 'contact@khaitri.edu.vn',
            'contact_phone' => '0867 852 853',
            'contact_address' => 'Long Xuyen, An Giang',
            'footer_text' => 'Khai Tri Edu - Cong dang ky khoa hoc truc tuyen',
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
