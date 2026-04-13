<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\Quiz;
use App\Models\CourseVideo;
use App\Models\QuizAttempt;

class DashboardController extends Controller
{
    public function index()
    {
        $instructorId = auth()->id();

        $instructorClasses = CourseClass::query()
            ->where('instructor_id', $instructorId)
            ->get(['id', 'course_id']);

        $classIds = $instructorClasses->pluck('id');
        $courseIds = $instructorClasses->pluck('course_id')->filter()->unique()->values();

        $stats = [
            'total_courses' => Course::whereIn('id', $courseIds)->count(),
            'total_quizzes' => Quiz::whereIn('course_id', $courseIds)->count(),
            'total_videos' => CourseVideo::whereIn('course_id', $courseIds)->count(),
            'total_students' => \App\Models\CourseEnrollment::whereIn('class_id', $classIds)
                ->whereIn('status', ['approved', 'completed'])
                ->count(),
            'recent_quiz_attempts' => QuizAttempt::with(['quiz.course', 'user'])
                ->whereIn('quiz_id', Quiz::whereIn('course_id', $courseIds)->pluck('id'))
                ->latest()
                ->take(5)
                ->get(),
        ];

        $recentCourses = Course::query()
            ->with(['category', 'classes', 'modules'])
            ->whereIn('id', $courseIds)
            ->latest()
            ->take(5)
            ->get();

        return view('instructor.dashboard', compact('stats', 'recentCourses'));
    }
}
