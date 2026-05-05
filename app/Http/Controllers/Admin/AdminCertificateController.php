<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\CourseEnrollment;
use App\Services\CsvExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCertificateController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->filteredQuery($request);

        $enrollments = $query->latest('completed_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $courses = Course::orderBy('title')->get(['id', 'title']);

        $stats = [
            'completed' => CourseEnrollment::completed()->count(),
            'with_certificate' => CourseEnrollment::completed()
                ->whereHas('certificate')
                ->count(),
        ];
        $stats['without_certificate'] = max(0, $stats['completed'] - $stats['with_certificate']);

        return view('admin.certificates.index', compact('enrollments', 'courses', 'stats'));
    }

    public function issue(Request $request, CourseEnrollment $enrollment)
    {
        $enrollment->loadMissing(['user', 'course', 'courseClass', 'certificate']);

        if (! $enrollment->isCompleted()) {
            return back()->with('error', 'Học viên chưa hoàn thành khóa học, không thể cấp chứng chỉ.');
        }

        if ($enrollment->certificate) {
            return back()->with('error', 'Học viên đã có chứng chỉ cho khóa học này.');
        }

        if (! $enrollment->course) {
            return back()->with('error', 'Không xác định được khóa học của đăng ký.');
        }

        CourseCertificate::create([
            'course_id' => $enrollment->course_id,
            'enrollment_id' => $enrollment->id,
            'user_id' => $enrollment->user_id,
            'certificate_no' => 'KTE-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6)),
            'issued_at' => now(),
            'meta' => [
                'issued_by' => 'admin',
                'admin_id' => $request->user()?->id,
            ],
        ]);

        return back()->with('success', 'Đã cấp chứng chỉ cho học viên ' . ($enrollment->user->fullname ?? $enrollment->user->username ?? 'này') . '.');
    }

    public function revoke(CourseCertificate $certificate)
    {
        $certificate->delete();

        return back()->with('success', 'Đã thu hồi chứng chỉ.');
    }

    public function export(Request $request, CsvExportService $csvExportService)
    {
        $enrollments = $this->filteredQuery($request)->latest('completed_at')->get();

        return $csvExportService->download(
            'certificates-' . now()->format('Y-m-d-His') . '.csv',
            ['ID dang ky', 'Hoc vien', 'Email', 'Khoa hoc', 'Lop', 'Ngay hoan thanh', 'Ma chung chi', 'Ngay cap'],
            $enrollments->map(function (CourseEnrollment $enrollment) {
                $cert = $enrollment->certificate;

                return [
                    $enrollment->id,
                    $enrollment->user->fullname ?? $enrollment->user->username ?? '',
                    $enrollment->user->email ?? '',
                    $enrollment->course->title ?? '',
                    $enrollment->courseClass->name ?? '',
                    optional($enrollment->completed_at)->format('d/m/Y H:i'),
                    $cert->certificate_no ?? 'Chua cap',
                    optional($cert?->issued_at)->format('d/m/Y H:i') ?: '',
                ];
            })
        );
    }

    protected function filteredQuery(Request $request)
    {
        $query = CourseEnrollment::with([
            'user:id,username,fullname,email',
            'course:id,title,delivery_mode,category_id',
            'course.category:id,name',
            'courseClass:id,course_id,name,start_date,end_date',
            'certificate',
        ])->completed();

        if ($request->filled('course_id')) {
            $query->forCourse($request->course_id);
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }

        if ($request->filled('certificate_status')) {
            $status = (string) $request->certificate_status;
            if ($status === 'with') {
                $query->whereHas('certificate');
            } elseif ($status === 'without') {
                $query->whereDoesntHave('certificate');
            }
        }

        if ($request->filled('search')) {
            $term = (string) $request->search;
            $query->whereHas('user', function ($userQuery) use ($term) {
                $userQuery->where('fullname', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%")
                    ->orWhere('username', 'like', "%{$term}%");
            });
        }

        return $query;
    }
}
