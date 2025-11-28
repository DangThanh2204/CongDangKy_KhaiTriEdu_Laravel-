<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CourseController extends Controller
{
    // Hiển thị danh sách khóa học
    public function index(Request $request)
    {
        $query = Course::with(['category', 'instructor'])
                      ->published();

        // Lọc theo category
        if ($request->has('category') && $request->category) {
            $query->byCategory($request->category);
        }

        // Lọc theo level
        if ($request->has('level') && $request->level) {
            $query->where('level', $request->level);
        }

        // Lọc theo featured/popular
        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'featured':
                    $query->featured();
                    break;
                case 'popular':
                    $query->popular();
                    break;
            }
        }

        $courses = $query->orderBy('created_at', 'desc')
                        ->paginate(12);

        $enrolledCourses = CourseEnrollment::where('user_id', Auth::id())
                                        ->where('status', 'approved')
                                        ->pluck('course_id')
                                        ->toArray();

        return view('courses.index', compact('courses', 'enrolledCourses'));
    }

    // Hiển thị chi tiết khóa học
    public function show(Course $course)
    {
        if ($course->status !== 'published') {
            abort(404);
        }

        $isEnrolled = CourseEnrollment::where('user_id', Auth::id())
                                    ->where('course_id', $course->id)
                                    ->where('status', 'approved')
                                    ->exists();

        $isPending = CourseEnrollment::where('user_id', Auth::id())
                                   ->where('course_id', $course->id)
                                   ->where('status', 'pending')
                                   ->exists();

        $similarCourses = Course::published()
                               ->where('id', '!=', $course->id)
                               ->where('category_id', $course->category_id)
                               ->limit(4)
                               ->get();

        return view('courses.show', compact('course', 'isEnrolled', 'isPending', 'similarCourses'));
    }
}