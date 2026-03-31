<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Quiz;
use App\Models\CourseVideo;
use App\Models\QuizAttempt;

class DashboardController extends Controller
{
    public function index()
    {
        $instructorId = auth()->id();

        // Get all class IDs taught by this instructor
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');

        // Get all course IDs from those classes
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        $stats = [
            'total_courses' => Course::whereIn('id', $courseIds)->count(),
            'total_quizzes' => Quiz::whereIn('course_id', $courseIds)->count(),
            'total_videos' => CourseVideo::whereIn('course_id', $courseIds)->count(),
            'total_students' => \App\Models\CourseEnrollment::whereIn('class_id', $classIds)
                ->where('status', 'approved')
                ->count(),
            'recent_quiz_attempts' => QuizAttempt::with(['quiz.course', 'user'])
                ->whereIn('quiz_id', Quiz::whereIn('course_id', $courseIds)->pluck('id'))
                ->latest()
                ->take(5)
                ->get(),
        ];

        $recentCourses = Course::whereIn('id', $courseIds)->latest()->take(5)->get();

        return view('instructor.dashboard', compact('stats', 'recentCourses'));
    }
}