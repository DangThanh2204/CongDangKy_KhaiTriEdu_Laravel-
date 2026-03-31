<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Support\StudentLevel;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $enrollments = CourseEnrollment::with(['class.course.category', 'class.course.instructor'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $approvedCourses = $enrollments->where('status', 'approved');
        $pendingCourses = $enrollments->where('status', 'pending');
        $completedCourses = $enrollments->filter(fn ($enrollment) => $enrollment->isCompleted());
        $studentLevel = $user->buildStudentLevelSummary();
        $leaderboard = StudentLevel::buildLeaderboard(10, $user->id);

        return view('student.dashboard', compact(
            'user',
            'enrollments',
            'approvedCourses',
            'pendingCourses',
            'completedCourses',
            'studentLevel',
            'leaderboard'
        ));
    }
}