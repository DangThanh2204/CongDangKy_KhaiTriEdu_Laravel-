<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ExtendedDataSeeder extends Seeder
{
    public function run()
    {
        // Clear existing data
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        \App\Models\User::truncate();
        \App\Models\CourseCategory::truncate();
        \App\Models\Course::truncate();
        \App\Models\PostCategory::truncate();
        \App\Models\Post::truncate();
        // ========== TẠO COURSE CATEGORIES ==========
        $courseCategories = [
            [
                'name' => 'Lập trình Web', 'slug' => 'lap-trinh-web', 'description' => 'Các khóa học lập trình web fullstack',
                'icon' => '💻', 'color' => '#3498db', 'order' => 1, 'is_active' => true
            ],
            [
                'name' => 'Lập trình Mobile', 'slug' => 'lap-trinh-mobile', 'description' => 'Phát triển ứng dụng di động',
                'icon' => '📱', 'color' => '#e74c3c', 'order' => 2, 'is_active' => true
            ],
            [
                'name' => 'Khoa học Dữ liệu', 'slug' => 'khoa-hoc-du-lieu', 'description' => 'Data Science, AI và Machine Learning',
                'icon' => '📊', 'color' => '#2ecc71', 'order' => 3, 'is_active' => true
            ],
            [
                'name' => 'Thiết kế Đồ họa', 'slug' => 'thiet-ke-do-hoa', 'description' => 'UI/UX Design và Graphic Design',
                'icon' => '🎨', 'color' => '#9b59b6', 'order' => 4, 'is_active' => true
            ],
            [
                'name' => 'Digital Marketing', 'slug' => 'digital-marketing', 'description' => 'Marketing online và SEO',
                'icon' => '📈', 'color' => '#f39c12', 'order' => 5, 'is_active' => true
            ],
            [
                'name' => 'Kinh doanh & Khởi nghiệp', 'slug' => 'kinh-doanh-khoi-nghiep', 'description' => 'Kiến thức kinh doanh và startup',
                'icon' => '💼', 'color' => '#34495e', 'order' => 6, 'is_active' => true
            ],
            [
                'name' => 'Ngoại ngữ', 'slug' => 'ngoai-ngu', 'description' => 'Học tiếng Anh và các ngoại ngữ khác',
                'icon' => '🌎', 'color' => '#1abc9c', 'order' => 7, 'is_active' => true
            ]
        ];

        foreach ($courseCategories as $category) {
            \App\Models\CourseCategory::create($category);
        }

        $categories = \App\Models\CourseCategory::all();

        // ========== TẠO COURSES ==========
        $courses = [
            // Web Development Courses
            [
                'title' => 'Laravel Mastery - Xây dựng ứng dụng web chuyên nghiệp',
                'slug' => 'laravel-mastery', 'price' => 899000, 'sale_price' => 599000,
                'thumbnail' => 'courses/laravel-thumb.jpg', 'banner_image' => 'courses/laravel-banner.jpg',
                'level' => 'intermediate', 'duration' => 45, 'lessons_count' => 28, 'students_count' => 1250,
                'rating' => 4.8, 'total_rating' => 342, 'category_id' => $categories[0]->id,
                'instructor_id' => $instructors[0]->id, 'is_featured' => true, 'is_popular' => true
            ],
            [
                'title' => 'ReactJS & NextJS - Phát triển ứng dụng web hiện đại',
                'slug' => 'reactjs-nextjs', 'price' => 799000, 'sale_price' => 549000,
                'thumbnail' => 'courses/react-thumb.jpg', 'banner_image' => 'courses/react-banner.jpg',
                'level' => 'beginner', 'duration' => 36, 'lessons_count' => 24, 'students_count' => 890,
                'rating' => 4.7, 'total_rating' => 215, 'category_id' => $categories[0]->id,
                'instructor_id' => $instructors[1]->id, 'is_featured' => true, 'is_popular' => false
            ],
            [
                'title' => 'Vue.js Complete Guide - Từ cơ bản đến nâng cao',
                'slug' => 'vuejs-complete-guide', 'price' => 699000, 'sale_price' => 499000,
                'thumbnail' => 'courses/vue-thumb.jpg', 'banner_image' => 'courses/vue-banner.jpg',
                'level' => 'beginner', 'duration' => 32, 'lessons_count' => 22, 'students_count' => 670,
                'rating' => 4.6, 'total_rating' => 156, 'category_id' => $categories[0]->id,
                'instructor_id' => $instructors[2]->id, 'is_featured' => false, 'is_popular' => true
            ],
            [
                'title' => 'Node.js & Express - Backend Development Mastery',
                'slug' => 'nodejs-express-backend', 'price' => 849000, 'sale_price' => 649000,
                'thumbnail' => 'courses/nodejs-thumb.jpg', 'banner_image' => 'courses/nodejs-banner.jpg',
                'level' => 'intermediate', 'duration' => 40, 'lessons_count' => 26, 'students_count' => 540,
                'rating' => 4.5, 'total_rating' => 123, 'category_id' => $categories[0]->id,
                'instructor_id' => $instructors[0]->id, 'is_featured' => true, 'is_popular' => false
            ],

            // Mobile Development Courses
            [
                'title' => 'Flutter Complete - Lập trình ứng dụng di động đa nền tảng',
                'slug' => 'flutter-complete', 'price' => 999000, 'sale_price' => 699000,
                'thumbnail' => 'courses/flutter-thumb.jpg', 'banner_image' => 'courses/flutter-banner.jpg',
                'level' => 'beginner', 'duration' => 52, 'lessons_count' => 35, 'students_count' => 670,
                'rating' => 4.9, 'total_rating' => 178, 'category_id' => $categories[1]->id,
                'instructor_id' => $instructors[0]->id, 'is_featured' => false, 'is_popular' => true
            ],
            [
                'title' => 'React Native - Xây dựng app mobile với JavaScript',
                'slug' => 'react-native-mobile', 'price' => 899000, 'sale_price' => 599000,
                'thumbnail' => 'courses/react-native-thumb.jpg', 'banner_image' => 'courses/react-native-banner.jpg',
                'level' => 'intermediate', 'duration' => 38, 'lessons_count' => 25, 'students_count' => 420,
                'rating' => 4.4, 'total_rating' => 98, 'category_id' => $categories[1]->id,
                'instructor_id' => $instructors[1]->id, 'is_featured' => false, 'is_popular' => true
            ],
            [
                'title' => 'iOS Development với SwiftUI',
                'slug' => 'ios-development-swiftui', 'price' => 1199000, 'sale_price' => 899000,
                'thumbnail' => 'courses/ios-thumb.jpg', 'banner_image' => 'courses/ios-banner.jpg',
                'level' => 'beginner', 'duration' => 48, 'lessons_count' => 30, 'students_count' => 320,
                'rating' => 4.7, 'total_rating' => 87, 'category_id' => $categories[1]->id,
                'instructor_id' => $instructors[2]->id, 'is_featured' => true, 'is_popular' => false
            ],

            // Data Science Courses
            [
                'title' => 'Python for Data Science & Machine Learning',
                'slug' => 'python-data-science', 'price' => 1299000, 'sale_price' => 999000,
                'thumbnail' => 'courses/python-ds-thumb.jpg', 'banner_image' => 'courses/python-ds-banner.jpg',
                'level' => 'beginner', 'duration' => 60, 'lessons_count' => 40, 'students_count' => 780,
                'rating' => 4.8, 'total_rating' => 234, 'category_id' => $categories[2]->id,
                'instructor_id' => $instructors[3]->id, 'is_featured' => true, 'is_popular' => true
            ],
            [
                'title' => 'Machine Learning từ cơ bản đến nâng cao',
                'slug' => 'machine-learning-complete', 'price' => 1599000, 'sale_price' => 1199000,
                'thumbnail' => 'courses/ml-thumb.jpg', 'banner_image' => 'courses/ml-banner.jpg',
                'level' => 'advanced', 'duration' => 72, 'lessons_count' => 45, 'students_count' => 450,
                'rating' => 4.9, 'total_rating' => 167, 'category_id' => $categories[2]->id,
                'instructor_id' => $instructors[0]->id, 'is_featured' => true, 'is_popular' => false
            ],

            // Design Courses
            [
                'title' => 'UI/UX Design Fundamentals - Thiết kế trải nghiệm người dùng',
                'slug' => 'ui-ux-design-fundamentals', 'price' => 799000, 'sale_price' => 549000,
                'thumbnail' => 'courses/uiux-thumb.jpg', 'banner_image' => 'courses/uiux-banner.jpg',
                'level' => 'beginner', 'duration' => 35, 'lessons_count' => 20, 'students_count' => 890,
                'rating' => 4.6, 'total_rating' => 198, 'category_id' => $categories[3]->id,
                'instructor_id' => $instructors[3]->id, 'is_featured' => false, 'is_popular' => true
            ],
            [
                'title' => 'Adobe Photoshop Masterclass 2024',
                'slug' => 'adobe-photoshop-masterclass', 'price' => 699000, 'sale_price' => 499000,
                'thumbnail' => 'courses/photoshop-thumb.jpg', 'banner_image' => 'courses/photoshop-banner.jpg',
                'level' => 'beginner', 'duration' => 28, 'lessons_count' => 18, 'students_count' => 1200,
                'rating' => 4.7, 'total_rating' => 312, 'category_id' => $categories[3]->id,
                'instructor_id' => $instructors[2]->id, 'is_featured' => true, 'is_popular' => true
            ],

            // Digital Marketing Courses
            [
                'title' => 'SEO Mastery - Tối ưu hóa công cụ tìm kiếm',
                'slug' => 'seo-mastery', 'price' => 599000, 'sale_price' => 399000,
                'thumbnail' => 'courses/seo-thumb.jpg', 'banner_image' => 'courses/seo-banner.jpg',
                'level' => 'beginner', 'duration' => 25, 'lessons_count' => 16, 'students_count' => 950,
                'rating' => 4.5, 'total_rating' => 187, 'category_id' => $categories[4]->id,
                'instructor_id' => $instructors[1]->id, 'is_featured' => false, 'is_popular' => true
            ],
            [
                'title' => 'Facebook Ads & Google Ads Complete Guide',
                'slug' => 'facebook-google-ads', 'price' => 899000, 'sale_price' => 649000,
                'thumbnail' => 'courses/ads-thumb.jpg', 'banner_image' => 'courses/ads-banner.jpg',
                'level' => 'intermediate', 'duration' => 32, 'lessons_count' => 22, 'students_count' => 680,
                'rating' => 4.6, 'total_rating' => 154, 'category_id' => $categories[4]->id,
                'instructor_id' => $instructors[0]->id, 'is_featured' => true, 'is_popular' => false
            ],

            // Business Courses
            [
                'title' => 'Khởi nghiệp thành công - Startup Fundamentals',
                'slug' => 'khoi-nghiep-thanh-cong', 'price' => 499000, 'sale_price' => 299000,
                'thumbnail' => 'courses/startup-thumb.jpg', 'banner_image' => 'courses/startup-banner.jpg',
                'level' => 'beginner', 'duration' => 20, 'lessons_count' => 15, 'students_count' => 1100,
                'rating' => 4.4, 'total_rating' => 245, 'category_id' => $categories[5]->id,
                'instructor_id' => $instructors[3]->id, 'is_featured' => false, 'is_popular' => true
            ],

            // Language Courses
            [
                'title' => 'Tiếng Anh giao tiếp cho Developer',
                'slug' => 'tieng-anh-giao-tiep-developer', 'price' => 399000, 'sale_price' => 249000,
                'thumbnail' => 'courses/english-dev-thumb.jpg', 'banner_image' => 'courses/english-dev-banner.jpg',
                'level' => 'beginner', 'duration' => 30, 'lessons_count' => 25, 'students_count' => 1500,
                'rating' => 4.3, 'total_rating' => 378, 'category_id' => $categories[6]->id,
                'instructor_id' => $instructors[2]->id, 'is_featured' => true, 'is_popular' => true
            ]
        ];

        foreach ($courses as $courseData) {
            $course = \App\Models\Course::create(array_merge($courseData, [
                'description' => 'Mô tả chi tiết cho khóa học ' . $courseData['title'] . '. Đây là khóa học chất lượng cao với nội dung đầy đủ và thực tế.',
                'short_description' => 'Khóa học ' . $courseData['title'] . ' - Học và thực hành ngay',
                'status' => 'published',
                'meta' => [
                    'prerequisites' => ['Kiến thức cơ bản'],
                    'what_you_learn' => ['Kỹ năng chuyên môn', 'Thực hành thực tế'],
                    'target_audience' => ['Người mới bắt đầu', 'Developer']
                ]
            ]));
        }

        // ========== TẠO POST CATEGORIES ==========
        $postCategories = [
            ['name' => 'Công nghệ', 'slug' => 'cong-nghe', 'color' => '#3498db', 'order' => 1, 'is_active' => true],
            ['name' => 'Giáo dục', 'slug' => 'giao-duc', 'color' => '#2ecc71', 'order' => 2, 'is_active' => true],
            ['name' => 'Nghề nghiệp', 'slug' => 'nghe-nghiep', 'color' => '#e74c3c', 'order' => 3, 'is_active' => true],
            ['name' => 'Khởi nghiệp', 'slug' => 'khoi-nghiep', 'color' => '#f39c12', 'order' => 4, 'is_active' => true],
            ['name' => 'Xu hướng', 'slug' => 'xu-huong', 'color' => '#9b59b6', 'order' => 5, 'is_active' => true],
            ['name' => 'Mẹo & Thủ thuật', 'slug' => 'meo-thu-thuat', 'color' => '#1abc9c', 'order' => 6, 'is_active' => true]
        ];

        foreach ($postCategories as $category) {
            \App\Models\PostCategory::create($category);
        }

        $postCats = \App\Models\PostCategory::all();

        // ========== TẠO POSTS ==========
        $posts = [
            // Công nghệ
            [
                'title' => 'Xu hướng AI 2024: ChatGPT và Beyond',
                'slug' => 'xu-huong-ai-2024-chatgpt', 'category_id' => $postCats[0]->id,
                'featured_image' => 'posts/ai-trends-2024.jpg', 'author_id' => $admins[0]->id,
                'view_count' => 2450, 'published_at' => now()->subDays(2)
            ],
            [
                'title' => 'Blockchain và Web3: Tương lai của Internet',
                'slug' => 'blockchain-web3-tuong-lai-internet', 'category_id' => $postCats[0]->id,
                'featured_image' => 'posts/blockchain-web3.jpg', 'author_id' => $instructors[0]->id,
                'view_count' => 1870, 'published_at' => now()->subDays(5)
            ],
            [
                'title' => 'Cloud Computing: AWS vs Azure vs Google Cloud',
                'slug' => 'cloud-computing-aws-azure-google', 'category_id' => $postCats[0]->id,
                'featured_image' => 'posts/cloud-computing.jpg', 'author_id' => $instructors[1]->id,
                'view_count' => 1320, 'published_at' => now()->subDays(7)
            ],
            [
                'title' => 'DevOps cho người mới bắt đầu',
                'slug' => 'devops-cho-nguoi-moi', 'category_id' => $postCats[0]->id,
                'featured_image' => 'posts/devops-beginner.jpg', 'author_id' => $instructors[2]->id,
                'view_count' => 980, 'published_at' => now()->subDays(10)
            ],

            // Giáo dục
            [
                'title' => 'Học lập trình online: Lộ trình cho người mới',
                'slug' => 'hoc-lap-trinh-online-lo-trinh', 'category_id' => $postCats[1]->id,
                'featured_image' => 'posts/learn-programming-roadmap.jpg', 'author_id' => $instructors[0]->id,
                'view_count' => 3250, 'published_at' => now()->subDays(1)
            ],
            [
                'title' => 'Phương pháp học tập hiệu quả cho developer',
                'slug' => 'phuong-phap-hoc-tap-hieu-qua', 'category_id' => $postCats[1]->id,
                'featured_image' => 'posts/effective-learning.jpg', 'author_id' => $instructors[1]->id,
                'view_count' => 2100, 'published_at' => now()->subDays(3)
            ],
            [
                'title' => 'Tự học Data Science trong 6 tháng',
                'slug' => 'tu-hoc-data-science-6-thang', 'category_id' => $postCats[1]->id,
                'featured_image' => 'posts/self-learn-data-science.jpg', 'author_id' => $instructors[3]->id,
                'view_count' => 1560, 'published_at' => now()->subDays(6)
            ],

            // Nghề nghiệp
            [
                'title' => 'Kỹ năng cần thiết cho developer 2024',
                'slug' => 'ky-nang-can-thiet-developer-2024', 'category_id' => $postCats[2]->id,
                'featured_image' => 'posts/developer-skills-2024.jpg', 'author_id' => $instructors[0]->id,
                'view_count' => 2890, 'published_at' => now()->subDays(1)
            ],
            [
                'title' => 'Làm freelance developer: Bắt đầu như thế nào?',
                'slug' => 'freelance-developer-bat-dau', 'category_id' => $postCats[2]->id,
                'featured_image' => 'posts/freelance-developer.jpg', 'author_id' => $instructors[1]->id,
                'view_count' => 1670, 'published_at' => now()->subDays(4)
            ],
            [
                'title' => 'Phỏng vấn developer: Chuẩn bị gì để thành công?',
                'slug' => 'phong-van-developer-chuan-bi', 'category_id' => $postCats[2]->id,
                'featured_image' => 'posts/developer-interview.jpg', 'author_id' => $instructors[2]->id,
                'view_count' => 1980, 'published_at' => now()->subDays(8)
            ],

            // Khởi nghiệp
            [
                'title' => 'Ý tưởng startup công nghệ 2024',
                'slug' => 'y-tuong-startup-cong-nghe-2024', 'category_id' => $postCats[3]->id,
                'featured_image' => 'posts/startup-ideas-2024.jpg', 'author_id' => $instructors[3]->id,
                'view_count' => 2340, 'published_at' => now()->subDays(2)
            ],
            [
                'title' => 'Pitch deck thành công: 10 slide cần có',
                'slug' => 'pitch-deck-thanh-cong-10-slide', 'category_id' => $postCats[3]->id,
                'featured_image' => 'posts/pitch-deck-success.jpg', 'author_id' => $admins[0]->id,
                'view_count' => 1450, 'published_at' => now()->subDays(5)
            ],

            // Xu hướng
            [
                'title' => 'Low-code/No-code: Tương lai của phát triển ứng dụng',
                'slug' => 'low-code-no-code-tuong-lai', 'category_id' => $postCats[4]->id,
                'featured_image' => 'posts/low-code-future.jpg', 'author_id' => $instructors[0]->id,
                'view_count' => 1780, 'published_at' => now()->subDays(3)
            ],
            [
                'title' => 'Metaverse: Cơ hội và thách thức cho developer',
                'slug' => 'metaverse-co-hoi-developer', 'category_id' => $postCats[4]->id,
                'featured_image' => 'posts/metaverse-developer.jpg', 'author_id' => $instructors[1]->id,
                'view_count' => 1560, 'published_at' => now()->subDays(7)
            ],

            // Mẹo & Thủ thuật
            [
                'title' => '10 extension VSCode không thể thiếu cho developer',
                'slug' => '10-extension-vscode-khong-the-thieu', 'category_id' => $postCats[5]->id,
                'featured_image' => 'posts/vscode-extensions.jpg', 'author_id' => $instructors[2]->id,
                'view_count' => 3120, 'published_at' => now()->subDays(1)
            ],
            [
                'title' => 'Git commands mà mọi developer nên biết',
                'slug' => 'git-commands-developer-nen-biet', 'category_id' => $postCats[5]->id,
                'featured_image' => 'posts/git-commands.jpg', 'author_id' => $instructors[0]->id,
                'view_count' => 2670, 'published_at' => now()->subDays(4)
            ],
            [
                'title' => 'Tối ưu hóa hiệu suất website với 10 bước đơn giản',
                'slug' => 'toi-uu-hieu-su-website-10-buoc', 'category_id' => $postCats[5]->id,
                'featured_image' => 'posts/website-performance.jpg', 'author_id' => $instructors[1]->id,
                'view_count' => 1890, 'published_at' => now()->subDays(6)
            ],
            [
                'title' => 'Debug JavaScript hiệu quả: Tips và Tricks',
                'slug' => 'debug-javascript-hieu-qua', 'category_id' => $postCats[5]->id,
                'featured_image' => 'posts/debug-javascript.jpg', 'author_id' => $instructors[2]->id,
                'view_count' => 1430, 'published_at' => now()->subDays(9)
            ]
        ];

        foreach ($posts as $postData) {
            \App\Models\Post::create(array_merge($postData, [
                'excerpt' => 'Bài viết về ' . $postData['title'] . ' - Khám phá ngay những thông tin hữu ích và cập nhật nhất.',
                'content' => '<p>Nội dung chi tiết của bài viết ' . $postData['title'] . '. Đây là bài viết chất lượng với thông tin được nghiên cứu kỹ lưỡng.</p><p>Chúng tôi cung cấp cho bạn những insights giá trị và thực tế.</p>',
                'status' => 'published',
                'meta' => [
                    'is_featured' => rand(0, 1) == 1,
                    'reading_time' => rand(3, 10),
                    'tags' => ['Công nghệ', 'Giáo dục', 'Developer']
                ]
            ]));
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('Extended sample data seeded successfully!');
        $this->command->info('Total: ' . \App\Models\Course::count() . ' courses, ' . \App\Models\Post::count() . ' posts');
    }
}