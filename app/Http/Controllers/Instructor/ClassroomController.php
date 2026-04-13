<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Services\CsvExportService;

class ClassroomController extends Controller
{
    public function index()
    {
        $instructorId = auth()->id();

        $classes = CourseClass::query()
            ->with(['course.category', 'enrollments.user'])
            ->where('instructor_id', $instructorId)
            ->orderBy('start_date')
            ->get()
            ->map(function (CourseClass $class) {
                $enrollments = $class->enrollments
                    ->sortByDesc(fn (CourseEnrollment $enrollment) => optional($enrollment->created_at)->timestamp ?? 0)
                    ->values();

                $class->setRelation('enrollments', $enrollments);

                return $class;
            });

        $stats = [
            'total_classes' => $classes->count(),
            'active_classes' => $classes->where('status', 'active')->count(),
            'students' => $classes->sum(fn (CourseClass $class) => $class->enrollments->whereIn('status', ['approved', 'completed'])->count()),
            'pending' => $classes->sum(fn (CourseClass $class) => $class->enrollments->where('status', 'pending')->count()),
            'waitlist' => $classes->sum(fn (CourseClass $class) => $class->waitlist_count),
        ];

        return view('instructor.classes.index', compact('classes', 'stats'));
    }

    public function exportStudents(CourseClass $courseClass, CsvExportService $csvExport)
    {
        $this->authorizeInstructorClass($courseClass);

        $courseClass->loadMissing(['course.category', 'enrollments.user']);

        $rows = $courseClass->enrollments
            ->sortBy([
                fn (CourseEnrollment $enrollment) => $enrollment->status !== 'approved' && $enrollment->status !== 'completed',
                fn (CourseEnrollment $enrollment) => optional($enrollment->created_at)->timestamp ?? 0,
            ])
            ->map(function (CourseEnrollment $enrollment) use ($courseClass): array {
                $student = $enrollment->user;

                return [
                    'Lop hoc' => $courseClass->name,
                    'Khoa hoc' => $courseClass->course?->title ?? '',
                    'Hoc vien' => $student?->fullname ?: $student?->username ?: '',
                    'Username' => $student?->username ?? '',
                    'Email' => $student?->email ?? '',
                    'Trang thai' => $enrollment->status_text,
                    'Ngay dang ky' => optional($enrollment->created_at)->format('d/m/Y H:i'),
                    'Ngay duyet' => optional($enrollment->approved_at)->format('d/m/Y H:i'),
                    'Ngay hoan thanh' => optional($enrollment->completed_at)->format('d/m/Y H:i'),
                    'Ghi chu' => $enrollment->notes ?? '',
                ];
            });

        $fileSafeClassName = str($courseClass->name)->slug('_');
        $filename = 'danh_sach_hoc_vien_' . ($fileSafeClassName ?: $courseClass->id) . '.csv';

        return $csvExport->download(
            $filename,
            ['Lớp học', 'Khóa học', 'Học viên', 'Username', 'Email', 'Trạng thái', 'Ngày đăng ký', 'Ngày duyệt', 'Ngày hoàn thành', 'Ghi chú'],
            $rows
        );
    }

    protected function authorizeInstructorClass(CourseClass $courseClass): void
    {
        if ((int) $courseClass->instructor_id !== (int) auth()->id()) {
            abort(403, 'Bạn không có quyền xem lớp học này.');
        }
    }
}
