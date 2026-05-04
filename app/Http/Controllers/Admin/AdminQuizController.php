<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Course;
use Illuminate\Http\Request;

class AdminQuizController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::with(['course', 'questions'])->paginate(15);
        return view('admin.quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        $courses = Course::published()->get();
        return view('admin.quizzes.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|exists:course_materials,id',
            'type' => 'required|in:pre_test,post_test,practice,exam',
            'time_limit' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1',
            'shuffle_questions' => 'boolean',
            'show_results' => 'boolean',
        ]);

        Quiz::create($validated);
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz created successfully');
    }

    public function show(Quiz $quiz)
    {
        $quiz->load('course', 'questions');
        return view('admin.quizzes.show', compact('quiz'));
    }

    public function edit(Quiz $quiz)
    {
        $courses = Course::published()->get();
        return view('admin.quizzes.edit', compact('quiz', 'courses'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|exists:course_materials,id',
            'type' => 'required|in:pre_test,post_test,practice,exam',
            'time_limit' => 'nullable|integer|min:1',
            'passing_score' => 'required|integer|min:0|max:100',
            'max_attempts' => 'required|integer|min:1',
            'shuffle_questions' => 'boolean',
            'show_results' => 'boolean',
        ]);

        $quiz->update($validated);
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz updated successfully');
    }

    public function destroy(Quiz $quiz)
    {
        $quiz->delete();
        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz deleted successfully');
    }

    public function questions(Quiz $quiz)
    {
        $questions = $quiz->questions()->orderBy('order')->get();
        return view('admin.quizzes.questions', compact('quiz', 'questions'));
    }

    public function storeQuestion(Request $request, Quiz $quiz)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer,essay',
            'options' => 'required_if:question_type,multiple_choice|array',
            'correct_answers' => 'required|array',
            'explanation' => 'nullable|string',
            'points' => 'required|integer|min:1',
            'order' => 'integer|min:0',
        ]);

        $quiz->questions()->create($validated);
        return redirect()->back()->with('success', 'Question added successfully');
    }

    public function updateQuestion(Request $request, Quiz $quiz, QuizQuestion $question)
    {
        $validated = $request->validate([
            'question_text' => 'required|string',
            'question_type' => 'required|in:multiple_choice,true_false,short_answer,essay',
            'options' => 'required_if:question_type,multiple_choice|array',
            'correct_answers' => 'required|array',
            'explanation' => 'nullable|string',
            'points' => 'required|integer|min:1',
            'order' => 'integer|min:0',
        ]);

        $question->update($validated);
        return redirect()->back()->with('success', 'Question updated successfully');
    }

    public function destroyQuestion(Quiz $quiz, QuizQuestion $question)
    {
        $question->delete();
        return redirect()->back()->with('success', 'Question deleted successfully');
    }

    public function attempts(Quiz $quiz)
    {
        $attempts = $quiz->attempts()->with('user')->paginate(20);
        return view('admin.quizzes.attempts', compact('quiz', 'attempts'));
    }
}
