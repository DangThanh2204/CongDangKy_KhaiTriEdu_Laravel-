<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    public function take(Quiz $quiz)
    {
        // Check if user can take this quiz
        if (!$quiz->canUserAttempt(auth()->id())) {
            return redirect()->back()->with('error', 'You have reached the maximum attempts for this quiz');
        }

        $questions = $quiz->shuffle_questions
            ? $quiz->questions()->active()->inRandomOrder()->get()
            : $quiz->questions()->active()->orderBy('order')->get();

        return view('student.quiz.take', compact('quiz', 'questions'));
    }

    public function start(Request $request, Quiz $quiz)
    {
        $attempt = QuizAttempt::create([
            'user_id' => auth()->id(),
            'quiz_id' => $quiz->id,
            'attempt_number' => $quiz->attempts()->where('user_id', auth()->id())->count() + 1,
            'total_questions' => $quiz->getTotalQuestions(),
            'total_points' => $quiz->getTotalPoints(),
            'started_at' => now(),
        ]);

        return redirect()->route('quizzes.take', $quiz)->with('attempt_id', $attempt->id);
    }

    public function saveAnswer(Request $request, QuizAttempt $attempt)
    {
        $validated = $request->validate([
            'question_id' => 'required|exists:quiz_questions,id',
            'answer' => 'required',
        ]);

        $question = QuizQuestion::findOrFail($validated['question_id']);

        $answer = QuizAnswer::updateOrCreate(
            [
                'attempt_id' => $attempt->id,
                'question_id' => $question->id,
            ],
            [
                'user_answer' => is_array($validated['answer']) ? $validated['answer'] : [$validated['answer']],
            ]
        );

        $answer->evaluate();

        return response()->json(['success' => true]);
    }

    public function complete(Request $request, QuizAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        $attempt->complete();

        return redirect()->route('quizzes.result', $attempt);
    }

    public function result(QuizAttempt $attempt)
    {
        if ($attempt->user_id !== auth()->id()) {
            abort(403);
        }

        $answers = $attempt->answers()->with('question')->get();

        return view('student.quiz.result', compact('attempt', 'answers'));
    }
}
