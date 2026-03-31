<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Course;

class QuizSeeder extends Seeder
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
                'description' => 'Khóa học mẫu để test quiz system',
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

        // Tạo quiz 1: Toán học cơ bản
        $quiz1 = Quiz::create([
            'title' => 'Bài kiểm tra Toán học cơ bản',
            'description' => 'Kiểm tra kiến thức toán học cơ bản cho học sinh lớp 1',
            'course_id' => $course->id,
            'type' => 'practice',
            'time_limit' => 30, // 30 phút
            'passing_score' => 70,
            'max_attempts' => 3,
            'is_active' => true,
            'shuffle_questions' => false,
            'show_results' => true,
        ]);

        // Câu hỏi cho quiz 1
        $questions1 = [
            [
                'question_text' => '1 + 1 bằng bao nhiêu?',
                'question_type' => 'multiple_choice',
                'options' => ['1', '2', '3', '4'],
                'correct_answers' => [1], // index của đáp án đúng
                'explanation' => '1 + 1 = 2 là phép cộng cơ bản nhất.',
                'points' => 1,
                'order' => 1,
            ],
            [
                'question_text' => '2 + 3 bằng bao nhiêu?',
                'question_type' => 'multiple_choice',
                'options' => ['4', '5', '6', '7'],
                'correct_answers' => [1],
                'explanation' => '2 + 3 = 5.',
                'points' => 1,
                'order' => 2,
            ],
            [
                'question_text' => 'Đúng hay Sai: 5 > 3',
                'question_type' => 'true_false',
                'correct_answers' => [0], // 0 = True, 1 = False
                'explanation' => '5 lớn hơn 3 là đúng.',
                'points' => 1,
                'order' => 3,
            ],
        ];

        foreach ($questions1 as $questionData) {
            QuizQuestion::create([
                'quiz_id' => $quiz1->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => isset($questionData['options']) ? json_encode($questionData['options']) : null,
                'correct_answers' => json_encode($questionData['correct_answers']),
                'explanation' => $questionData['explanation'],
                'points' => $questionData['points'],
                'order' => $questionData['order'],
            ]);
        }

        // Tạo quiz 2: Ngữ văn
        $quiz2 = Quiz::create([
            'title' => 'Bài kiểm tra Ngữ văn lớp 1',
            'description' => 'Kiểm tra kiến thức ngữ văn cơ bản',
            'course_id' => $course->id,
            'type' => 'practice',
            'time_limit' => 45,
            'passing_score' => 70,
            'max_attempts' => 3,
            'is_active' => true,
            'shuffle_questions' => false,
            'show_results' => true,
        ]);

        $questions2 = [
            [
                'question_text' => 'Từ nào sau đây là danh từ?',
                'question_type' => 'multiple_choice',
                'options' => ['Chạy', 'Bút', 'Đỏ', 'Nhanh'],
                'correct_answers' => [1],
                'explanation' => '"Bút" là danh từ chỉ đồ vật.',
                'points' => 1,
                'order' => 1,
            ],
            [
                'question_text' => 'Điền từ thích hợp: Con _____ bay trên trời.',
                'question_type' => 'short_answer',
                'correct_answers' => ['chim'],
                'explanation' => 'Con chim bay trên trời.',
                'points' => 2,
                'order' => 2,
            ],
            [
                'question_text' => 'Đúng hay Sai: "Ăn" là động từ',
                'question_type' => 'true_false',
                'correct_answers' => [0],
                'explanation' => '"Ăn" là động từ chỉ hành động.',
                'points' => 1,
                'order' => 3,
            ],
        ];

        foreach ($questions2 as $questionData) {
            QuizQuestion::create([
                'quiz_id' => $quiz2->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => isset($questionData['options']) ? json_encode($questionData['options']) : null,
                'correct_answers' => json_encode($questionData['correct_answers']),
                'explanation' => $questionData['explanation'],
                'points' => $questionData['points'],
                'order' => $questionData['order'],
            ]);
        }

        // Tạo quiz 3: Khoa học
        $quiz3 = Quiz::create([
            'title' => 'Bài kiểm tra Khoa học tự nhiên',
            'description' => 'Kiểm tra kiến thức về thế giới xung quanh',
            'course_id' => $course->id,
            'type' => 'practice',
            'time_limit' => 60,
            'passing_score' => 70,
            'max_attempts' => 3,
            'is_active' => true,
            'shuffle_questions' => false,
            'show_results' => true,
        ]);

        $questions3 = [
            [
                'question_text' => 'Mặt trời mọc ở hướng nào?',
                'question_type' => 'multiple_choice',
                'options' => ['Đông', 'Tây', 'Nam', 'Bắc'],
                'correct_answers' => [0],
                'explanation' => 'Mặt trời mọc ở hướng Đông.',
                'points' => 1,
                'order' => 1,
            ],
            [
                'question_text' => 'Nước có mấy trạng thái?',
                'question_type' => 'multiple_choice',
                'options' => ['1', '2', '3', '4'],
                'correct_answers' => [2],
                'explanation' => 'Nước có 3 trạng thái: rắn, lỏng, khí.',
                'points' => 1,
                'order' => 2,
            ],
            [
                'question_text' => 'Đúng hay Sai: Chim biết bay',
                'question_type' => 'true_false',
                'correct_answers' => [0],
                'explanation' => 'Hầu hết các loài chim đều biết bay.',
                'points' => 1,
                'order' => 3,
            ],
            [
                'question_text' => 'Giải thích tại sao lá cây màu xanh?',
                'question_type' => 'essay',
                'correct_answers' => ['Lá cây chứa chất diệp lục'],
                'explanation' => 'Lá cây chứa chất diệp lục (chlorophyll) nên có màu xanh.',
                'points' => 3,
                'order' => 4,
            ],
        ];

        foreach ($questions3 as $questionData) {
            QuizQuestion::create([
                'quiz_id' => $quiz3->id,
                'question_text' => $questionData['question_text'],
                'question_type' => $questionData['question_type'],
                'options' => isset($questionData['options']) ? json_encode($questionData['options']) : null,
                'correct_answers' => json_encode($questionData['correct_answers']),
                'explanation' => $questionData['explanation'],
                'points' => $questionData['points'],
                'order' => $questionData['order'],
            ]);
        }

        $this->command->info('Đã tạo thành công 3 quiz mẫu với tổng cộng ' . (count($questions1) + count($questions2) + count($questions3)) . ' câu hỏi!');
    }
}
