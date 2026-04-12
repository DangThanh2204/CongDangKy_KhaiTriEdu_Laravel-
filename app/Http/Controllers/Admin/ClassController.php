<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\User;
use App\Services\CsvExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ClassController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $courseId = $request->get('course_id');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $classes = $this->filteredQuery($request)
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();
        $this->attachEnrollmentCounts($classes->getCollection());

        $stats = [
            'total' => CourseClass::count(),
            'active' => CourseClass::where('status', 'active')->count(),
            'inactive' => CourseClass::where('status', 'inactive')->count(),
        ];

        $courses = Course::with('category')
            ->orderBy('title')
            ->get(['id', 'title', 'category_id', 'status']);

        return view('admin.classes.index', compact(
            'classes',
            'stats',
            'courses',
            'search',
            'status',
            'courseId',
            'fromDate',
            'toDate'
        ));
    }

    public function export(Request $request, CsvExportService $csvExportService)
    {
        $classes = $this->filteredQuery($request)
            ->orderBy('created_at', 'desc')
            ->get();

        return $csvExportService->download(
            'classes-' . now()->format('Y-m-d-His') . '.csv',
            ['ID', 'Tên đợt học', 'Khóa học', 'Giảng viên', 'Ngày bắt đầu', 'Ngày kết thúc', 'Lịch học', 'Thông tin gặp học', 'Sức chứa', 'Trạng thái'],
            $classes->map(function (CourseClass $class) {
                return [
                    $class->id,
                    $class->name,
                    $class->course->title ?? '',
                    $class->instructor?->fullname ?? $class->instructor?->username ?? '',
                    optional($class->start_date)->format('d/m/Y'),
                    optional($class->end_date)->format('d/m/Y'),
                    $class->schedule_text,
                    $class->meeting_info,
                    $class->max_students,
                    $class->status === 'active' ? 'Mở đăng ký' : 'Tạm dừng',
                ];
            })
        );
    }

    protected function filteredQuery(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $courseId = $request->get('course_id');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = CourseClass::with(['course.category', 'instructor', 'schedules']);

        if ($search) {
            $query->where(function ($innerQuery) use ($search) {
                $innerQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhereHas('course', function ($courseQuery) use ($search) {
                        $courseQuery->where('title', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        return $query;
    }

    public function create()
    {
        $courses = Course::with('category')
            ->orderBy('title')
            ->get(['id', 'title', 'category_id', 'status']);

        $instructors = User::where('role', 'instructor')
            ->orderBy('fullname')
            ->get(['id', 'fullname', 'email', 'username']);

        return view('admin.classes.create', compact('courses', 'instructors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:users,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'schedule' => 'nullable|string|max:255',
            'meeting_info' => 'nullable|string|max:1000',
            'max_students' => 'required|integer|min:0|max:1000',
            'price_override' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $courseClass = CourseClass::create($validated);
        $this->saveStructuredSchedules($courseClass, $request->input('schedules', []));

        return redirect()->route('admin.classes.index')
            ->with('success', 'Đợt học đã được tạo thành công!');
    }

    public function show(CourseClass $courseClass)
    {
        $courseClass->load(['course', 'instructor', 'schedules', 'enrollments.user']);

        return view('admin.classes.show', ['class' => $courseClass]);
    }

    public function edit(CourseClass $courseClass)
    {
        $courseClass->load(['schedules', 'course', 'instructor']);
        $this->attachEnrollmentCounts(collect([$courseClass]));

        $courses = Course::with('category')
            ->orderBy('title')
            ->get(['id', 'title', 'category_id', 'status']);

        $instructors = User::where('role', 'instructor')
            ->orderBy('fullname')
            ->get(['id', 'fullname', 'email', 'username']);

        return view('admin.classes.edit', [
            'class' => $courseClass,
            'courses' => $courses,
            'instructors' => $instructors,
        ]);
    }

    public function update(Request $request, CourseClass $courseClass)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'instructor_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'schedule' => 'nullable|string|max:255',
            'meeting_info' => 'nullable|string|max:1000',
            'max_students' => 'required|integer|min:0|max:1000',
            'price_override' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $courseClass->update($validated);
        $this->saveStructuredSchedules($courseClass, $request->input('schedules', []));

        return redirect()->route('admin.classes.index')
            ->with('success', 'Đợt học đã được cập nhật thành công!');
    }

    protected function saveStructuredSchedules(CourseClass $courseClass, array $schedules)
    {
        $courseClass->schedules()->delete();

        $allowedWeekdays = ['2', '3', '4', '5', '6', '7', 'CN'];
        foreach ($schedules as $weekday => $slots) {
            if (! in_array($weekday, $allowedWeekdays)) {
                continue;
            }

            if (! $slots || ! is_array($slots)) {
                continue;
            }

            if (isset($slots['start']) || isset($slots['end'])) {
                $slots = [$slots];
            }

            foreach ($slots as $slot) {
                $start = isset($slot['start']) ? trim($slot['start']) : null;
                $end = isset($slot['end']) ? trim($slot['end']) : null;

                if (! $start && ! $end) {
                    continue;
                }

                if ($start && ! preg_match('/^\d{1,2}:\d{2}$/', $start)) {
                    continue;
                }

                if ($end && ! preg_match('/^\d{1,2}:\d{2}$/', $end)) {
                    continue;
                }

                $courseClass->schedules()->create([
                    'weekday' => $weekday,
                    'start_time' => $start,
                    'end_time' => $end,
                ]);
            }
        }
    }

    private function attachEnrollmentCounts(Collection $classes): void
    {
        if ($classes->isEmpty()) {
            return;
        }

        $countsByClass = collect();

        foreach ($classes->pluck('id')->all() as $classId) {
            $countsByClass[(string) $classId] = 0;
        }

        \App\Models\CourseEnrollment::query()
            ->whereIn('class_id', $classes->pluck('id')->all())
            ->get(['class_id'])
            ->each(function ($enrollment) use ($countsByClass) {
                $classKey = (string) $enrollment->class_id;
                $countsByClass[$classKey] = (int) ($countsByClass[$classKey] ?? 0) + 1;
            });

        $classes->each(function (CourseClass $courseClass) use ($countsByClass) {
            $courseClass->setAttribute('enrollments_count', (int) ($countsByClass[(string) $courseClass->id] ?? 0));
        });
    }

    public function destroy(CourseClass $courseClass)
    {
        if ($courseClass->enrollments()->count() > 0) {
            return redirect()->route('admin.classes.index')
                ->with('error', 'Không thể xóa đợt học đã có học viên đăng ký!');
        }

        $courseClass->delete();

        return redirect()->route('admin.classes.index')
            ->with('success', 'Đợt học đã được xóa thành công!');
    }
}
