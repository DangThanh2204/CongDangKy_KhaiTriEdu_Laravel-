<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Services\CsvExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminEnrollmentController extends Controller
{
    public function pendingEnrollments(Request $request)
    {
        $query = $this->offlinePendingQuery($request);

        $enrollments = (clone $query)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $pendingCount = CourseEnrollment::pending()
            ->whereHas('courseClass.course', fn ($courseQuery) => $courseQuery->deliveryMode('offline'))
            ->count();

        $approvedCount = CourseEnrollment::whereIn('status', ['approved', 'completed'])
            ->whereHas('courseClass.course', fn ($courseQuery) => $courseQuery->deliveryMode('offline'))
            ->count();

        $rejectedCount = CourseEnrollment::whereIn('status', ['rejected', 'cancelled'])
            ->whereHas('courseClass.course', fn ($courseQuery) => $courseQuery->deliveryMode('offline'))
            ->count();

        $courses = Course::deliveryMode('offline')
            ->orderBy('title')
            ->get(['id', 'title', 'learning_type']);

        return view('admin.enrollments.pending', compact(
            'enrollments',
            'pendingCount',
            'approvedCount',
            'rejectedCount',
            'courses'
        ));
    }

    public function exportPending(Request $request, CsvExportService $csvExportService)
    {
        $enrollments = $this->offlinePendingQuery($request)->latest()->get();

        return $this->exportCsv($csvExportService, $enrollments, 'pending-enrollments-');
    }

    public function showManualCreate()
    {
        $courses = Course::with([
                'category',
                'classes' => function ($query) {
                    $query->orderByRaw("CASE WHEN status = 'active' THEN 0 ELSE 1 END")
                        ->orderBy('start_date')
                        ->orderBy('id');
                },
            ])
            ->orderBy('title')
            ->get();

        return view('admin.enrollments.manual-create', compact('courses'));
    }

    public function manualEnroll(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'class_id' => [
                'required',
                Rule::exists('classes', 'id')->where(function ($query) use ($request) {
                    $query->where('course_id', $request->input('course_id'));
                }),
            ],
            'email' => 'required|email',
            'fullname' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
            'auto_approve' => 'boolean',
        ]);

        try {
            $class = CourseClass::with(['course.category'])->findOrFail($validated['class_id']);
            $course = $class->course;
            $requiresManualApproval = $course->requiresManualApproval();

            if ($class->status !== 'active') {
                return back()->withInput()->with('error', 'Đợt học đã chọn hiện chưa mở đăng ký.');
            }

            if ($requiresManualApproval && $class->is_full) {
                return back()->withInput()->with('error', 'Đợt học đã chọn đã đủ số lượng học viên.');
            }

            $user = User::where('email', $validated['email'])->first();
            if (! $user) {
                $user = User::create([
                    'username' => $this->generateUsernameFromName($validated['fullname']),
                    'fullname' => $validated['fullname'],
                    'email' => $validated['email'],
                    'password' => Hash::make(Str::random(12)),
                    'role' => 'student',
                    'is_verified' => true,
                ]);
            }

            $existing = CourseEnrollment::where('user_id', $user->id)
                ->forCourse($course)
                ->whereIn('status', ['pending', 'approved', 'completed'])
                ->first();

            if ($existing) {
                return back()->withInput()->with('error', 'Học viên đã có đăng ký cho khóa học này rồi.');
            }

            $shouldApprove = ! $requiresManualApproval || $request->boolean('auto_approve');

            $enrollment = CourseEnrollment::firstOrNew([
                'user_id' => $user->id,
                'class_id' => $class->id,
            ]);

            $enrollment->forceFill([
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'enrolled_at' => null,
                'approved_at' => null,
                'rejected_at' => null,
                'cancelled_at' => null,
                'completed_at' => null,
            ])->save();

            if ($shouldApprove) {
                $enrollment->approve();
            }

            $message = $course->isOnline()
                ? 'Đã ghi danh học viên thành công. Học viên có thể vào học ngay.'
                : ($shouldApprove
                    ? 'Đã thêm học viên vào đợt học và duyệt thành công.'
                    : 'Đã tạo yêu cầu đăng ký offline, vui lòng duyệt ở danh sách chờ.');

            return redirect()
                ->route($shouldApprove ? 'admin.enrollments.index' : 'admin.enrollments.pending')
                ->with('success', $message);
        } catch (\Throwable $exception) {
            return back()
                ->withInput()
                ->with('error', 'Có lỗi xảy ra: ' . $exception->getMessage());
        }
    }

    private function generateUsernameFromName(string $fullname): string
    {
        $normalized = Str::of($fullname)
            ->ascii()
            ->lower()
            ->replaceMatches('/[^a-z0-9\s]/', '')
            ->squish();

        $words = preg_split('/\s+/', (string) $normalized, -1, PREG_SPLIT_NO_EMPTY);
        if (empty($words)) {
            $baseUsername = 'student';
        } elseif (count($words) === 1) {
            $baseUsername = $words[0];
        } else {
            $baseUsername = $words[0] . end($words);
        }

        $baseUsername = substr($baseUsername, 0, 15);
        if (strlen($baseUsername) < 3) {
            $baseUsername .= 'user';
        }

        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
            if ($counter > 1000) {
                $username = $baseUsername . time();
                break;
            }
        }

        return $username;
    }

    public function approveEnrollment(CourseEnrollment $enrollment)
    {
        $enrollment->loadMissing(['courseClass.course']);

        if (! $enrollment->isPending()) {
            return back()->with('error', 'Yêu cầu này đã được xử lý.');
        }

        if ($enrollment->courseClass?->course?->isOffline() && $enrollment->courseClass?->is_full) {
            return back()->with('error', 'Đợt học đã đầy. Không thể duyệt đăng ký này.');
        }

        $enrollment->approve();

        return back()->with('success', 'Đã duyệt đăng ký thành công.');
    }

    public function rejectEnrollment(Request $request, CourseEnrollment $enrollment)
    {
        if (! $enrollment->isPending()) {
            return back()->with('error', 'Yêu cầu này đã được xử lý.');
        }

        $request->validate([
            'rejection_notes' => 'required|string|max:500',
        ]);

        $enrollment->reject($request->rejection_notes);

        return back()->with('success', 'Đã từ chối đăng ký.');
    }

    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);

        $enrollments = $query->latest()->paginate(20)->withQueryString();
        $courses = Course::orderBy('title')->get(['id', 'title', 'learning_type']);

        return view('admin.enrollments.index', compact('enrollments', 'courses'));
    }

    public function export(Request $request, CsvExportService $csvExportService)
    {
        $enrollments = $this->filteredQuery($request)->latest()->get();

        return $this->exportCsv($csvExportService, $enrollments, 'enrollments-');
    }

    protected function filteredQuery(Request $request)
    {
        $query = CourseEnrollment::with(['user', 'course', 'course.category', 'course.instructor', 'courseClass']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('course_id')) {
            $query->forCourse($request->course_id);
        }

        if ($request->filled('delivery_mode')) {
            $query->whereHas('courseClass.course', function ($courseQuery) use ($request) {
                $courseQuery->deliveryMode($request->string('delivery_mode')->toString());
            });
        }

        return $query;
    }

    protected function exportCsv(CsvExportService $csvExportService, $enrollments, string $prefix)
    {
        return $csvExportService->download(
            $prefix . now()->format('Y-m-d-His') . '.csv',
            ['ID', 'Hoc vien', 'Email', 'Khoa hoc', 'Hinh thuc', 'Dot hoc', 'Trang thai', 'Cho con lai', 'Ngay dang ky'],
            $enrollments->map(function (CourseEnrollment $enrollment) {
                return [
                    $enrollment->id,
                    $enrollment->user->fullname ?? $enrollment->user->username ?? '',
                    $enrollment->user->email ?? '',
                    $enrollment->course->title ?? '',
                    $enrollment->course?->delivery_mode_label ?? '',
                    $enrollment->courseClass->name ?? '',
                    $enrollment->status_text,
                    $enrollment->courseClass?->remaining_slots,
                    optional($enrollment->created_at)->format('d/m/Y H:i'),
                ];
            })
        );
    }

    public function destroy(CourseEnrollment $enrollment)
    {
        if (in_array($enrollment->status, ['approved', 'completed'], true) && $enrollment->course) {
            $enrollment->course->decrement('students_count');
        }

        $enrollment->delete();

        return back()->with('success', 'Đã xóa đăng ký.');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:course_enrollments,id',
        ]);

        $enrollments = CourseEnrollment::whereIn('id', $request->enrollment_ids)
            ->pending()
            ->with(['courseClass.course'])
            ->get();

        $approvedCount = 0;
        $skippedCount = 0;

        foreach ($enrollments as $enrollment) {
            if ($enrollment->courseClass?->course?->isOffline() && $enrollment->courseClass?->is_full) {
                $skippedCount++;
                continue;
            }

            $enrollment->approve();
            $approvedCount++;
        }

        $message = 'Đã duyệt ' . $approvedCount . ' đăng ký.';
        if ($skippedCount > 0) {
            $message .= ' Bỏ qua ' . $skippedCount . ' đăng ký vì đợt học đã đầy.';
        }

        return back()->with('success', $message);
    }

    private function offlinePendingQuery(Request $request)
    {
        $query = CourseEnrollment::with(['user', 'course', 'course.category', 'course.instructor', 'courseClass'])
            ->pending()
            ->whereHas('courseClass.course', fn ($courseQuery) => $courseQuery->deliveryMode('offline'));

        if ($request->filled('course_id')) {
            $query->forCourse($request->course_id);
        }

        return $query;
    }
}