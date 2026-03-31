<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CourseCategory;

class CourseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Toán học',
                'slug' => 'toan-hoc',
                'description' => 'Các khóa học về toán học từ cơ bản đến nâng cao',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Ngữ văn',
                'slug' => 'ngu-van',
                'description' => 'Các khóa học về tiếng Việt và văn học',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Khoa học Tự nhiên',
                'slug' => 'khoa-hoc-tu-nhien',
                'description' => 'Các khóa học về khoa học, vật lý, hóa học, sinh học',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Lịch sử',
                'slug' => 'lich-su',
                'description' => 'Các khóa học về lịch sử Việt Nam và thế giới',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Địa lý',
                'slug' => 'dia-ly',
                'description' => 'Các khóa học về địa lý và kiến thức bản đồ',
                'parent_id' => null,
                'is_active' => true,
            ],
            [
                'name' => 'Tiếng Anh',
                'slug' => 'tieng-anh',
                'description' => 'Các khóa học tiếng Anh từ cơ bản đến nâng cao',
                'parent_id' => null,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            CourseCategory::create($category);
        }

        $this->command->info('Đã tạo thành công ' . count($categories) . ' danh mục khóa học!');
    }
}
