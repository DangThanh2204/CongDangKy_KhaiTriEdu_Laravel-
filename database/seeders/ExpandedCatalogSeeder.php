<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseClass;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Bổ sung catalog phong phú để trang khóa học trông như site thật:
 * 8 nhóm ngành, gần 20 khóa, mỗi khóa có ít nhất 1 đợt học mở.
 *
 * Idempotent: chạy lại không nhân đôi bản ghi (slug + tên class là khoá).
 */
class ExpandedCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = User::where('role', 'instructor')->first()
            ?? User::where('role', 'admin')->first();

        if (! $instructor) {
            $this->command?->warn('ExpandedCatalogSeeder bỏ qua vì chưa có user instructor/admin.');

            return;
        }

        $categories = $this->seedCategories();
        $courses = $this->seedCourses($categories, $instructor);
        $this->seedClasses($courses, $instructor);

        $this->command?->info('ExpandedCatalogSeeder: đã thêm ' . count($courses) . ' khóa học và lớp đi kèm.');
    }

    private function seedCategories(): array
    {
        $items = [
            ['cong-nghe-thong-tin', 'Công nghệ thông tin', 'Lập trình, web, mobile, devops, AI.', '#1d4ed8', 1],
            ['marketing-kinh-doanh', 'Marketing - Kinh doanh', 'Tăng trưởng, nội dung, sales, thương hiệu.', '#f97316', 2],
            ['tieng-anh', 'Tiếng Anh', 'Giao tiếp, IELTS, TOEIC, business English.', '#0f766e', 3],
            ['thiet-ke-do-hoa', 'Thiết kế đồ họa', 'Photoshop, Illustrator, Figma, UI/UX.', '#a855f7', 4],
            ['khoa-hoc-du-lieu', 'Khoa học dữ liệu', 'Phân tích, ML, Power BI, Python data.', '#0891b2', 5],
            ['ky-nang-mem', 'Kỹ năng mềm', 'Giao tiếp, thuyết trình, quản lý thời gian.', '#65a30d', 6],
            ['quan-tri-kinh-doanh', 'Quản trị kinh doanh', 'Lãnh đạo, vận hành, tài chính cá nhân.', '#dc2626', 7],
            ['nhiep-anh-video', 'Nhiếp ảnh - Video', 'Chụp ảnh, dựng phim, làm content video.', '#0d9488', 8],
        ];

        $map = [];
        foreach ($items as [$slug, $name, $description, $color, $order]) {
            $map[$slug] = CourseCategory::updateOrCreate(
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

        return $map;
    }

    /**
     * @return array<string, Course>
     */
    private function seedCourses(array $categories, User $instructor): array
    {
        $entries = [
            // IT
            ['lap-trinh-python-cho-nguoi-moi', 'Lập trình Python cho người mới', 'cong-nghe-thong-tin', 'beginner', 'online', 1490000, 1190000, true, true,
                'Học Python từ con số 0: cú pháp, vòng lặp, hàm, OOP, làm các project nhỏ thực tế.',
                'Lộ trình 6 tuần, có bài tập, quiz, mini project chấm tự động.'],
            ['lap-trinh-react-thuc-chien', 'Lập trình React thực chiến', 'cong-nghe-thong-tin', 'intermediate', 'online', 2890000, 2390000, true, false,
                'Hooks, context, react-query, routing, build app SPA hoàn chỉnh.',
                'Khóa nâng cao cho người đã biết JavaScript.'],
            ['nodejs-va-rest-api', 'Node.js và xây dựng REST API', 'cong-nghe-thong-tin', 'intermediate', 'online', 2290000, 1890000, false, true,
                'Express, Sequelize, JWT auth, deploy lên cloud, viết API chuẩn REST.',
                'Phù hợp dev backend mới và sinh viên CNTT năm 3-4.'],
            ['flutter-mobile-app', 'Phát triển ứng dụng mobile với Flutter', 'cong-nghe-thong-tin', 'intermediate', 'online', 2790000, 2390000, true, false,
                'Dart, widget tree, state management, kết nối API, publish CH Play.',
                'Làm 2 app hoàn chỉnh trong khóa.'],
            ['docker-devops-co-ban', 'Docker và DevOps cơ bản', 'cong-nghe-thong-tin', 'advanced', 'online', 1990000, 1690000, false, false,
                'Docker, Docker Compose, CI/CD với GitHub Actions, deploy production.',
                'Khóa thực hành, mỗi buổi đều có lab.'],

            // Marketing
            ['facebook-ads-tu-co-ban-den-nang-cao', 'Facebook Ads từ cơ bản đến nâng cao', 'marketing-kinh-doanh', 'beginner', 'online', 1690000, 1290000, true, true,
                'Cài Pixel, target audience, A/B test, scaling, tối ưu CPC/CPA.',
                'Cập nhật theo Meta Ads 2026.'],
            ['seo-website-tang-traffic', 'SEO website - tăng traffic tự nhiên', 'marketing-kinh-doanh', 'intermediate', 'online', 1890000, 1490000, false, false,
                'On-page, off-page, content cluster, technical SEO, Google Search Console.',
                'Có audit website thật của học viên.'],
            ['email-marketing-automation', 'Email marketing & automation', 'marketing-kinh-doanh', 'beginner', 'online', 1290000, 990000, false, false,
                'Mailchimp, drip campaign, segment, đo lường mở/click/conversion.',
                'Phù hợp shop online, agency nhỏ.'],

            // English
            ['ielts-foundation-4-5', 'IELTS Foundation - mục tiêu 4.5', 'tieng-anh', 'beginner', 'offline', 3990000, 3490000, true, true,
                'Học 4 kỹ năng từ ngữ pháp căn bản đến viết task 1 đơn giản.',
                'Lớp tối đa 12 học viên tại trung tâm.'],
            ['toeic-650-cap-toc', 'TOEIC 650 cấp tốc', 'tieng-anh', 'intermediate', 'offline', 3490000, 2990000, false, true,
                'Tập trung Listening + Reading, mock test hàng tuần.',
                'Khóa 8 tuần, 3 buổi/tuần.'],
            ['business-english-for-it', 'Tiếng Anh giao tiếp cho dân IT', 'tieng-anh', 'intermediate', 'online', 1990000, 1690000, false, false,
                'Email, daily standup, technical writing, phỏng vấn dev quốc tế.',
                'Online 100%, có buổi speaking 1-1.'],

            // Design
            ['photoshop-co-ban-cho-nguoi-moi', 'Photoshop cơ bản cho người mới', 'thiet-ke-do-hoa', 'beginner', 'online', 1490000, 1190000, true, true,
                'Layer, mask, retouch ảnh, ghép ảnh, làm banner mạng xã hội.',
                'Có file thực hành theo từng buổi.'],
            ['ui-ux-design-voi-figma', 'UI/UX Design với Figma', 'thiet-ke-do-hoa', 'intermediate', 'online', 2490000, 2090000, true, false,
                'Wireframe, prototype, design system, hand-off cho dev.',
                'Cuối khóa có portfolio 3 dự án.'],
            ['illustrator-thiet-ke-vector', 'Illustrator - thiết kế vector', 'thiet-ke-do-hoa', 'beginner', 'online', 1490000, 1190000, false, false,
                'Logo, icon, infographic, in ấn, chuẩn bị file in ấn.',
                'Khóa nhanh 4 tuần.'],

            // Data
            ['phan-tich-du-lieu-voi-excel', 'Phân tích dữ liệu với Excel', 'khoa-hoc-du-lieu', 'beginner', 'online', 1290000, 990000, false, true,
                'Pivot table, dashboard, công thức nâng cao, Power Query.',
                'Phù hợp dân văn phòng, kế toán, marketing.'],
            ['power-bi-cho-nguoi-moi', 'Power BI cho người mới bắt đầu', 'khoa-hoc-du-lieu', 'beginner', 'online', 1690000, 1390000, true, false,
                'Kết nối nguồn dữ liệu, DAX cơ bản, build báo cáo tương tác.',
                'Có dataset thật để thực hành.'],

            // Soft skills
            ['ky-nang-thuyet-trinh', 'Kỹ năng thuyết trình trước đám đông', 'ky-nang-mem', 'beginner', 'offline', 1490000, 1190000, false, true,
                'Cấu trúc bài nói, ngôn ngữ cơ thể, chống run, dùng slide hiệu quả.',
                'Lớp offline có thực hành quay video.'],
            ['quan-ly-thoi-gian-hieu-qua', 'Quản lý thời gian hiệu quả', 'ky-nang-mem', 'beginner', 'online', 890000, 690000, false, false,
                'Pomodoro, GTD, eisenhower matrix, lập kế hoạch tuần.',
                'Khóa ngắn 2 tuần, video + worksheet.'],

            // Business
            ['khoi-nghiep-tinh-gon-lean-startup', 'Khởi nghiệp tinh gọn (Lean Startup)', 'quan-tri-kinh-doanh', 'intermediate', 'online', 1990000, 1690000, true, false,
                'MVP, validate ý tưởng, business model canvas, pitch deck.',
                'Có buổi mentor 1-1 cuối khóa.'],
            ['tai-chinh-ca-nhan-co-ban', 'Tài chính cá nhân cơ bản', 'quan-tri-kinh-doanh', 'beginner', 'online', 990000, 790000, false, true,
                'Quản lý chi tiêu, đầu tư, lập quỹ khẩn cấp, đọc báo cáo tài chính.',
                'Phù hợp người mới đi làm.'],

            // Photo / Video
            ['lam-video-tiktok-cho-nguoi-moi', 'Làm video TikTok cho người mới', 'nhiep-anh-video', 'beginner', 'online', 1290000, 990000, true, true,
                'Quay bằng điện thoại, dựng CapCut, trend, hook, tăng tương tác.',
                'Cuối khóa lên kênh thật của học viên.'],
        ];

        $map = [];
        foreach ($entries as [$slug, $title, $categorySlug, $level, $learningType, $price, $salePrice, $isFeatured, $isPopular, $description, $shortDescription]) {
            $course = Course::withTrashed()->firstOrNew(['slug' => $slug]);
            $course->fill([
                'title' => $title,
                'slug' => $slug,
                'description' => $description,
                'short_description' => $shortDescription,
                'category_id' => $categories[$categorySlug]->id,
                'instructor_id' => $instructor->id,
                'price' => $price,
                'sale_price' => $salePrice,
                'level' => $level,
                'status' => 'published',
                'is_featured' => $isFeatured,
                'is_popular' => $isPopular,
                'learning_type' => $learningType,
                'announcement' => $learningType === 'online'
                    ? 'Khoá online - đăng ký xong vào học ngay.'
                    : 'Khoá offline - cần admin duyệt hồ sơ trước khi vào lớp.',
            ]);
            $course->deleted_at = null;
            $course->save();

            $map[$slug] = $course;
        }

        return $map;
    }

    private function seedClasses(array $courses, User $instructor): void
    {
        foreach ($courses as $slug => $course) {
            $isOffline = $course->learning_type === 'offline';
            $startOffset = $isOffline ? mt_rand(7, 25) : -mt_rand(0, 14);
            $duration = $isOffline ? 60 : 45;
            $name = $isOffline
                ? 'Khoá ' . str_pad((string) mt_rand(1, 6), 2, '0', STR_PAD_LEFT) . ' - tháng ' . now()->addDays($startOffset)->format('m')
                : 'Học ngay - kỳ tháng ' . now()->format('m');

            $class = CourseClass::firstOrNew([
                'course_id' => $course->id,
                'name' => $name,
            ]);

            $class->fill([
                'instructor_id' => $instructor->id,
                'start_date' => now()->addDays($startOffset)->toDateString(),
                'end_date' => now()->addDays($startOffset + $duration)->toDateString(),
                'schedule' => $isOffline
                    ? 'Thứ ' . collect([2, 4, 6])->random() . ', ' . collect([3, 5, 7])->random() . ' | 18:30 - 20:00'
                    : 'Tự học linh hoạt trên hệ thống',
                'meeting_info' => $isOffline
                    ? 'Phòng ' . collect(['A105', 'A203', 'B201', 'B305'])->random() . ' - cơ sở Long Xuyên'
                    : 'Workshop live thứ 7 lúc 20:00 (Google Meet)',
                'max_students' => $isOffline ? mt_rand(15, 25) : 0,
                'price_override' => null,
                'status' => 'active',
            ]);

            $class->course_id = $course->id;
            $class->save();
        }
    }
}
