<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnrollmentController extends Controller
{
    public function enroll(Request $request, Course $course)
    {
        // Kiểm tra khóa học có published không
        if ($course->status !== 'published') {
            return back()->with('error', 'Khóa học không khả dụng!');
        }

        // Kiểm tra đã đăng ký chưa
        $existingEnrollment = CourseEnrollment::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->first();

        if ($existingEnrollment) {
            if ($existingEnrollment->isPending()) {
                return back()->with('error', 'Bạn đã gửi yêu cầu đăng ký, đang chờ duyệt!');
            }
            if ($existingEnrollment->isApproved()) {
                return back()->with('error', 'Bạn đã đăng ký khóa học này!');
            }
        }

        // Tạo enrollment - KHÔNG sử dụng requires_approval
        $enrollment = CourseEnrollment::create([
            'user_id' => Auth::id(),
            'course_id' => $course->id,
            'status' => 'pending', // Mặc định cần phê duyệt
            'enrolled_at' => now()
        ]);

        $message = 'Đã gửi yêu cầu đăng ký. Vui lòng chờ phê duyệt từ quản trị viên!';

        return back()->with('success', $message);
    }

    public function unenroll(Course $course)
    {
        $enrollment = CourseEnrollment::where('user_id', Auth::id())
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return back()->with('error', 'Bạn chưa đăng ký khóa học này!');
        }

        $enrollment->delete();

        return back()->with('success', 'Đã hủy đăng ký khóa học!');
    }
}