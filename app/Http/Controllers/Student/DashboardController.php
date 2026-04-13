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
            ->get()
            ->map(function (CourseEnrollment $enrollment) {
                $class = $enrollment->class;
                $course = $enrollment->course ?: $class?->course;

                if ($course) {
                    $enrollment->setRelation('course', $course);
                }

                if ($class) {
                    $enrollment->setRelation('courseClass', $class);
                }

                return $enrollment;
            });

        $approvedCourses = $enrollments->where('status', 'approved');
        $pendingCourses = $enrollments->where('status', 'pending');
        $completedCourses = $enrollments->filter(fn ($enrollment) => $enrollment->isCompleted());

        try {
            $studentLevel = $user->buildStudentLevelSummary();
        } catch (\Throwable $exception) {
            report($exception);
            $studentLevel = StudentLevel::emptyProfile();
        }

        try {
            $leaderboard = StudentLevel::buildLeaderboard(10, $user->id);
        } catch (\Throwable $exception) {
            report($exception);
            $leaderboard = [
                'entries' => collect(),
                'current_user' => null,
                'total_students' => 0,
            ];
        }

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
