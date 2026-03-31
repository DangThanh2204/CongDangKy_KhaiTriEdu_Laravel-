<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    public function index(Request $request, Course $course)
    {
        $user = auth()->user();

        // Get class IDs taught by this instructor for this course
        $classIds = $course->classes()->where('instructor_id', $user->id)->pluck('id');

        if ($classIds->isEmpty() && !$user->isAdmin()) {
            abort(403);
        }

        $query = CourseEnrollment::with('user')
            ->whereIn('class_id', $classIds);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $enrollments = $query->latest()->paginate(20);

        return view('instructor.enrollments.index', compact('course', 'enrollments'));
    }

    public function approve(Course $course, CourseEnrollment $enrollment)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        if ($enrollment->courseClass->course_id != $course->id) {
            abort(404);
        }

        if ($enrollment->isPending()) {
            // Check class capacity
            $class = $enrollment->courseClass;
            if ($class->is_full) {
                return back()->with('error', 'Lớp đã đầy. Không thể duyệt đăng ký này.');
            }

            $enrollment->approve();
        }

        return back()->with('success', 'Đã duyệt đăng ký.');
    }

    public function reject(Request $request, Course $course, CourseEnrollment $enrollment)
    {
        $user = auth()->user();
        if (!$user->isAdmin()) {
            abort(403);
        }

        if ($enrollment->courseClass->course_id != $course->id) {
            abort(404);
        }

        $request->validate(['rejection_notes' => 'nullable|string|max:500']);

        if ($enrollment->isPending()) {
            $enrollment->reject($request->rejection_notes);
        }

        return back()->with('success', 'Đã từ chối đăng ký.');
    }

    public function destroy(Course $course, CourseEnrollment $enrollment)
    {
        $user = auth()->user();
        $classIds = $course->classes()->where('instructor_id', $user->id)->pluck('id');
        if (!$classIds->contains($enrollment->class_id) && !$user->isAdmin()) {
            abort(403);
        }

        if ($enrollment->courseClass->course_id != $course->id) {
            abort(404);
        }

        if ($enrollment->isApproved()) {
            $enrollment->courseClass->course->decrement('students_count');
        }

        $enrollment->delete();

        return back()->with('success', 'Đã xóa đăng ký.');
    }
}