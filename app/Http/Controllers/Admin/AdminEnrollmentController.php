<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseEnrollment;
use App\Models\Course;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminEnrollmentController extends Controller
{
    // Danh sách yêu cầu đăng ký chờ duyệt
    public function pendingEnrollments()
    {
        $enrollments = CourseEnrollment::with(['user', 'course'])
            ->pending()
            ->latest()
            ->paginate(15);

        $pendingCount = CourseEnrollment::pending()->count();
        $approvedCount = CourseEnrollment::approved()->count();
        $rejectedCount = CourseEnrollment::rejected()->count();

        return view('admin.enrollments.pending', compact(
            'enrollments', 
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }
    // Hiển thị form thêm học viên thủ công
    public function showManualCreate()
    {
        $courses = Course::where('status', 'published')->get();
        return view('admin.enrollments.manual-create', compact('courses'));
    }

    // Thêm học viên vào khóa học
    public function manualEnroll(Request $request)
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'email' => 'required|email',
            'fullname' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
            'auto_approve' => 'boolean'
        ]);

        try {
            // Tìm hoặc tạo user
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                // Tạo username từ tên học viên (viết tắt)
                $username = $this->generateUsernameFromName($request->fullname);
                
                // Tạo user mới
                $user = User::create([
                    'username' => $username,
                    'fullname' => $request->fullname,
                    'email' => $request->email,
                    'password' => Hash::make(Str::random(12)), // Random password
                    'role' => 'student',
                    'is_verified' => true, // Auto verify cho admin tạo
                ]);
            }

            // Kiểm tra xem user đã đăng ký khóa học này chưa
            $existingEnrollment = CourseEnrollment::where('user_id', $user->id)
                ->where('course_id', $request->course_id)
                ->first();

            if ($existingEnrollment) {
                return back()->with('error', 'Học viên đã đăng ký khóa học này trước đó!');
            }

            // Tạo enrollment
            $enrollment = CourseEnrollment::create([
                'user_id' => $user->id,
                'course_id' => $request->course_id,
                'status' => $request->auto_approve ? 'approved' : 'pending',
                'requires_approval' => false, // Không yêu cầu approval vì admin tạo
                'enrolled_at' => $request->auto_approve ? now() : null,
                'approved_at' => $request->auto_approve ? now() : null,
                'notes' => $request->notes,
            ]);

            // Cập nhật số lượng học viên nếu được duyệt ngay
            if ($request->auto_approve) {
                $course = Course::find($request->course_id);
                $course->increment('students_count');
            }

            $message = $request->auto_approve 
                ? "Đã thêm học viên vào khóa học và duyệt tự động!"
                : "Đã thêm học viên vào khóa học thành công!";

            // Thêm thông tin username mới tạo vào message
            if (!$user->wasRecentlyCreated) {
                $message .= " Username: {$user->username}";
            }

            return redirect()->route('admin.enrollments.pending')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Có lỗi xảy ra: ' . $e->getMessage());
        }
    }

    // Tạo username từ tên (viết tắt)
    private function generateUsernameFromName($fullname)
    {
        // Chuẩn hóa tên: loại bỏ khoảng trắng thừa và chuyển về chữ thường không dấu
        $fullname = trim($fullname);
        $fullname = $this->removeAccents($fullname);
        
        // Tách thành các từ
        $words = explode(' ', $fullname);
        
        if (count($words) === 1) {
            // Nếu chỉ có 1 từ, lấy toàn bộ từ đó
            $baseUsername = strtolower($words[0]);
        } else {
            // Lấy chữ cái đầu của các từ đầu và từ cuối cùng
            $firstWord = $words[0];
            $lastWord = end($words);
            
            // Tạo username từ: firstWord + lastWord (viết thường, không dấu)
            $baseUsername = strtolower($firstWord . $lastWord);
        }
        
        // Loại bỏ các ký tự không hợp lệ
        $baseUsername = preg_replace('/[^a-z0-9]/', '', $baseUsername);
        
        // Đảm bảo độ dài tối thiểu
        if (strlen($baseUsername) < 3) {
            $baseUsername .= 'user';
        }
        
        // Giới hạn độ dài
        $baseUsername = substr($baseUsername, 0, 15);
        
        // Thêm số nếu username đã tồn tại
        $username = $baseUsername;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
            
            // Giới hạn số lần thử
            if ($counter > 1000) {
                $username = $baseUsername . time(); // Fallback: thêm timestamp
                break;
            }
        }
        
        return $username;
    }

    // Hàm loại bỏ dấu tiếng Việt
    private function removeAccents($string)
    {
        $string = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $string);
        $string = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $string);
        $string = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $string);
        $string = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $string);
        $string = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $string);
        $string = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $string);
        $string = preg_replace("/(đ)/", 'd', $string);
        $string = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $string);
        $string = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $string);
        $string = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $string);
        $string = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $string);
        $string = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $string);
        $string = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $string);
        $string = preg_replace("/(Đ)/", 'D', $string);
        
        return $string;
    }

    // Duyệt đăng ký
    public function approveEnrollment(CourseEnrollment $enrollment)
    {
        if (!$enrollment->isPending()) {
            return back()->with('error', 'Yêu cầu này đã được xử lý!');
        }

        $enrollment->approve();

        // Gửi thông báo cho học viên (có thể implement sau)
        // Notification::send($enrollment->user, new EnrollmentApproved($enrollment));

        return back()->with('success', 'Đã duyệt đăng ký thành công!');
    }

    // Từ chối đăng ký
    public function rejectEnrollment(Request $request, CourseEnrollment $enrollment)
    {
        if (!$enrollment->isPending()) {
            return back()->with('error', 'Yêu cầu này đã được xử lý!');
        }

        $request->validate([
            'rejection_notes' => 'required|string|max:500'
        ]);

        $enrollment->reject($request->rejection_notes);

        // Gửi thông báo cho học viên (có thể implement sau)
        // Notification::send($enrollment->user, new EnrollmentRejected($enrollment));

        return back()->with('success', 'Đã từ chối đăng ký!');
    }

    // Danh sách tất cả enrollment
    public function index(Request $request)
    {
        $query = CourseEnrollment::with(['user', 'course']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->course_id) {
            $query->where('course_id', $request->course_id);
        }

        $enrollments = $query->latest()->paginate(20);
        $courses = Course::all();

        return view('admin.enrollments.index', compact('enrollments', 'courses'));
    }

    // Xóa enrollment
    public function destroy(CourseEnrollment $enrollment)
    {
        if ($enrollment->isApproved()) {
            $enrollment->course->decrement('students_count');
        }

        $enrollment->delete();

        return back()->with('success', 'Đã xóa đăng ký!');
    }

    // Duyệt nhiều enrollment cùng lúc
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:course_enrollments,id'
        ]);

        $enrollments = CourseEnrollment::whereIn('id', $request->enrollment_ids)
            ->pending()
            ->get();

        foreach ($enrollments as $enrollment) {
            $enrollment->approve();
        }

        return back()->with('success', "Đã duyệt {$enrollments->count()} đăng ký!");
    }
}