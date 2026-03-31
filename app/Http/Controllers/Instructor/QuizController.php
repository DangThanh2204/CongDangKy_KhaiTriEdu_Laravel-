<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Course;

class QuizController extends Controller
{
    public function index()
    {
        $instructorId = auth()->id();

        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        $quizzes = Quiz::with(['course', 'questions'])
            ->whereIn('course_id', $courseIds)
            ->latest()
            ->paginate(10);

        return view('instructor.quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();
        $courses = Course::whereIn('id', $courseIds)->get();

        return view('instructor.quizzes.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'time_limit' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'shuffle_questions' => 'boolean',
            'show_results' => 'boolean',
        ]);

        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($request->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        Quiz::create($request->all());

        return redirect()->route('instructor.quizzes.index')
            ->with('success', 'Bài kiểm tra đã được tạo thành công!');
    }

    public function show(Quiz $quiz)
    {
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($quiz->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $quiz->load(['questions', 'course']);
        return view('instructor.quizzes.show', compact('quiz'));
    }

    public function edit(Quiz $quiz)
    {
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($quiz->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $courses = Course::whereIn('id', $courseIds)->get();

        return view('instructor.quizzes.edit', compact('quiz', 'courses'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($quiz->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'time_limit' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1',
            'is_active' => 'boolean',
            'shuffle_questions' => 'boolean',
            'show_results' => 'boolean',
        ]);

        // Ensure new course_id also belongs to instructor
        if (!in_array($request->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $quiz->update($request->all());

        return redirect()->route('instructor.quizzes.index')
            ->with('success', 'Bài kiểm tra đã được cập nhật thành công!');
    }

    public function destroy(Quiz $quiz)
    {
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($quiz->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $quiz->delete();

        return redirect()->route('instructor.quizzes.index')
            ->with('success', 'Bài kiểm tra đã được xóa thành công!');
    }

    public function questions(Quiz $quiz)
    {
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($quiz->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $questions = $quiz->questions()->orderBy('order')->get();
        return view('instructor.quizzes.questions', compact('quiz', 'questions'));
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($quiz->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer,essay',
            'options' => 'nullable|array',
            'correct_answers' => 'required|array',
            'explanation' => 'nullable|string',
            'points' => 'required|integer|min:1',
        ]);

        $maxOrder = $quiz->questions()->max('order') ?? 0;

        QuizQuestion::create([
            'quiz_id' => $quiz->id,
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'options' => $request->options ? json_encode($request->options) : null,
            'correct_answers' => json_encode($request->correct_answers),
            'explanation' => $request->explanation,
            'points' => $request->points,
            'order' => $maxOrder + 1,
        ]);

        return redirect()->back()->with('success', 'Câu hỏi đã được thêm thành công!');
    }

    public function updateQuestion(Request $request, QuizQuestion $question)
    {
        $quiz = $question->quiz;
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($quiz->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer,essay',
            'options' => 'nullable|array',
            'correct_answers' => 'required|array',
            'explanation' => 'nullable|string',
            'points' => 'required|integer|min:1',
        ]);

        $question->update([
            'question_text' => $request->question_text,
            'question_type' => $request->question_type,
            'options' => $request->options ? json_encode($request->options) : null,
            'correct_answers' => json_encode($request->correct_answers),
            'explanation' => $request->explanation,
            'points' => $request->points,
        ]);

        return redirect()->back()->with('success', 'Câu hỏi đã được cập nhật thành công!');
    }

    public function destroyQuestion(QuizQuestion $question)
    {
        $quiz = $question->quiz;
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($quiz->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $question->delete();

        return redirect()->back()->with('success', 'Câu hỏi đã được xóa thành công!');
    }
}