<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\CourseVideo;
use App\Models\Course;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lấy course đầu tiên hoặc tạo mới nếu chưa có
        $course = Course::first();
        if (!$course) {
            $course = Course::create([
                'title' => 'Khóa học mẫu',
                'slug' => 'khoa-hoc-mau',
                'description' => 'Khóa học mẫu để test video system',
                'short_description' => 'Khóa học mẫu',
                'price' => 100000,
                'instructor_id' => 1,
                'category_id' => 1,
                'status' => 'published',
                'level' => 'beginner',
                'duration' => '30',
                'lessons_count' => 10,
            ]);
        }

        // Tạo video mẫu (đã được xử lý sẵn)
        $videos = [
            [
                'title' => 'Giới thiệu về Toán học cơ bản',
                'description' => 'Bài giảng giới thiệu về các phép tính cơ bản trong toán học.',
                'original_filename' => 'math_intro.mp4',
                'video_path' => 'videos/math_intro.mp4',
                'hls_playlist_path' => 'videos/hls/math_intro.m3u8',
                'hls_segments_path' => 'videos/hls/math_intro/',
                'duration' => 930, // 15:30 in seconds
                'file_size' => 52428800, // 50MB
                'processing_status' => 'completed',
                'order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Học chữ cái A-B-C',
                'description' => 'Bài giảng về cách phát âm và viết chữ cái đầu tiên trong bảng chữ cái.',
                'original_filename' => 'alphabet_abc.mp4',
                'video_path' => 'videos/alphabet_abc.mp4',
                'hls_playlist_path' => 'videos/hls/alphabet_abc.m3u8',
                'hls_segments_path' => 'videos/hls/alphabet_abc/',
                'duration' => 765, // 12:45 in seconds
                'file_size' => 41943040, // 40MB
                'processing_status' => 'completed',
                'order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Thế giới động vật - Chim và Cá',
                'description' => 'Tìm hiểu về các loài chim và cá, đặc điểm và môi trường sống.',
                'original_filename' => 'animals_birds_fish.mp4',
                'video_path' => 'videos/animals_birds_fish.mp4',
                'hls_playlist_path' => 'videos/hls/animals_birds_fish.m3u8',
                'hls_segments_path' => 'videos/hls/animals_birds_fish/',
                'duration' => 1215, // 20:15 in seconds
                'file_size' => 73400320, // 70MB
                'processing_status' => 'completed',
                'order' => 3,
                'is_active' => true,
            ],
            [
                'title' => 'Màu sắc và hình dạng',
                'description' => 'Bài giảng về các màu cơ bản và các hình dạng đơn giản.',
                'original_filename' => 'colors_shapes.mp4',
                'video_path' => 'videos/colors_shapes.mp4',
                'hls_playlist_path' => 'videos/hls/colors_shapes.m3u8',
                'hls_segments_path' => 'videos/hls/colors_shapes/',
                'duration' => 1100, // 18:20 in seconds
                'file_size' => 62914560, // 60MB
                'processing_status' => 'completed',
                'order' => 4,
                'is_active' => true,
            ],
            [
                'title' => 'Nhạc cụ dân tộc Việt Nam',
                'description' => 'Giới thiệu về các nhạc cụ truyền thống của Việt Nam.',
                'original_filename' => 'vietnamese_instruments.mp4',
                'video_path' => 'videos/vietnamese_instruments.mp4',
                'hls_playlist_path' => 'videos/hls/vietnamese_instruments.m3u8',
                'hls_segments_path' => 'videos/hls/vietnamese_instruments/',
                'duration' => 1510, // 25:10 in seconds
                'file_size' => 83886080, // 80MB
                'processing_status' => 'completed',
                'order' => 5,
                'is_active' => true,
            ],
            [
                'title' => 'Đồ thị và biểu đồ',
                'description' => 'Cách đọc và phân tích các loại đồ thị và biểu đồ cơ bản.',
                'original_filename' => 'graphs_charts.mp4',
                'video_path' => 'videos/graphs_charts.mp4',
                'hls_playlist_path' => 'videos/hls/graphs_charts.m3u8',
                'hls_segments_path' => 'videos/hls/graphs_charts/',
                'duration' => 1335, // 22:35 in seconds
                'file_size' => 57671680, // 55MB
                'processing_status' => 'completed',
                'order' => 6,
                'is_active' => true,
            ],
        ];

        foreach ($videos as $videoData) {
            CourseVideo::create(array_merge($videoData, [
                'course_id' => $course->id,
            ]));
        }

        // Tạo một video đang xử lý
        CourseVideo::create([
            'course_id' => $course->id,
            'title' => 'Video đang được xử lý',
            'description' => 'Video này đang trong quá trình xử lý HLS.',
            'original_filename' => 'processing_video.mp4',
            'video_path' => 'videos/processing_video.mp4',
            'duration' => 600, // 10:00 in seconds
            'file_size' => 31457280, // 30MB
            'processing_status' => 'processing',
            'order' => 7,
            'is_active' => true,
        ]);

        // Tạo một video lỗi
        CourseVideo::create([
            'course_id' => $course->id,
            'title' => 'Video bị lỗi',
            'description' => 'Video này gặp lỗi trong quá trình xử lý.',
            'original_filename' => 'error_video.mp4',
            'video_path' => 'videos/error_video.mp4',
            'duration' => 390, // 08:30 in seconds
            'file_size' => 20971520, // 20MB
            'processing_status' => 'failed',
            'processing_error' => 'FFMpeg processing failed: Invalid video format',
            'order' => 8,
            'is_active' => false,
        ]);

        $this->command->info('Đã tạo thành công ' . count($videos) . ' video mẫu, 1 video đang xử lý và 1 video lỗi!');
    }
}
