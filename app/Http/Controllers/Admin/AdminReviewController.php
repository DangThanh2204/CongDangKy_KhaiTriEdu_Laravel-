<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseReview;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class AdminReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseReview::with(['course', 'user', 'instructor']);

        if ($request->has('course') && $request->course) {
            $query->where('course_id', $request->course);
        }

        if ($request->has('instructor') && $request->instructor) {
            $query->where('instructor_id', $request->instructor);
        }

        if ($request->has('rating') && $request->rating) {
            $query->where('rating', $request->rating);
        }

        $reviews = $query->latest()->paginate(15);

        session()->put('admin.notifications.reviews_seen_at', now()->toDateTimeString());

        $courses = Course::orderBy('title')->get(['id', 'title']);
        $instructors = User::where('role', 'instructor')->orderBy('fullname')->get(['id', 'fullname']);

        return view('admin.reviews.index', compact('reviews', 'courses', 'instructors'));
    }

    public function destroy(CourseReview $review)
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')->with('success', 'Đã xoá đánh giá thành công.');
    }
}
