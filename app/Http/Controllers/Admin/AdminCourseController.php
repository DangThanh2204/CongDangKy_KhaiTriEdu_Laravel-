<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use App\Services\CsvExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminCourseController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $category = $request->get('category');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $courses = $this->filteredQuery($request)
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $stats = [
            'totalCourses' => Course::count(),
            'publishedCourses' => Course::where('status', 'published')->count(),
            'draftCourses' => Course::where('status', 'draft')->count(),
            'featuredCourses' => Course::where('is_featured', true)->count(),
        ];

        $categories = CourseCategory::active()->get();

        return view('admin.courses.index', compact(
            'courses',
            'stats',
            'categories',
            'search',
            'status',
            'category',
            'fromDate',
            'toDate'
        ));
    }

    public function export(Request $request, CsvExportService $csvExportService)
    {
        $courses = $this->filteredQuery($request)->latest()->get();

        return $csvExportService->download(
            'courses-' . now()->format('Y-m-d-His') . '.csv',
            ['ID', 'Tieu de', 'Nhom nganh', 'Trang thai', 'Hinh thuc dao tao', 'Gia', 'Gia khuyen mai', 'So module', 'So dot hoc', 'So hoc vien', 'Ngay tao'],
            $courses->map(function (Course $course) {
                return [
                    $course->id,
                    $course->title,
                    $course->category->name ?? '',
                    $course->status,
                    $course->delivery_mode_label,
                    $course->price,
                    $course->sale_price,
                    $course->modules_count,
                    $course->classes_count,
                    $course->students_count,
                    optional($course->created_at)->format('d/m/Y H:i'),
                ];
            })
        );
    }

    protected function filteredQuery(Request $request)
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $category = $request->get('category');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = Course::with(['category', 'classes', 'modules'])
            ->withCount(['classes', 'modules']);

        if ($search) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($category) {
            $query->where('category_id', $category);
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
        $categories = CourseCategory::active()->get();
        $instructors = User::where('role', 'instructor')->get(['id', 'fullname', 'email']);

        return view('admin.courses.create', compact('categories', 'instructors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        $courseData = $this->extractCourseData($request, $validated);
        $courseData['slug'] = Str::slug($validated['title']);
        $courseData['lessons_count'] = 0;
        $courseData['students_count'] = 0;
        $courseData['rating'] = 0;
        $courseData['total_rating'] = 0;

        $course = Course::create($courseData);

        $this->syncModules($course, $request->input('modules', []));
        $this->syncClasses($course, $request->input('classes', []));
        $this->syncPrimaryInstructorFromClasses($course);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Khóa học đã được tạo thành công.');
    }

    public function edit(Course $course)
    {
        $categories = CourseCategory::active()->get();
        $instructors = User::where('role', 'instructor')->get(['id', 'fullname', 'email']);
        $course->load(['classes', 'modules']);

        return view('admin.courses.edit', compact('course', 'categories', 'instructors'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate($this->rules($course));

        $courseData = $this->extractCourseData($request, $validated, $course);
        if ($course->title !== $validated['title']) {
            $courseData['slug'] = Str::slug($validated['title']);
        }

        $course->update($courseData);

        $this->syncModules($course, $request->input('modules', []));
        $this->syncClasses($course, $request->input('classes', []));
        $this->syncPrimaryInstructorFromClasses($course->fresh(['classes']));

        return redirect()->route('admin.courses.index')
            ->with('success', 'Khóa học đã được cập nhật thành công.');
    }

    protected function rules(?Course $course = null): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'required|string|max:500',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'level' => 'required|in:beginner,intermediate,advanced,all',
            'duration' => 'nullable|integer|min:0',
            'category_id' => 'required|exists:course_categories,id',
            'status' => 'required|in:draft,published',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'is_featured' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
            'video_url' => 'nullable|url',
            'pdf' => 'nullable|file|mimes:pdf|max:10240',
            'learning_type' => 'required|in:online,offline',
            'announcement' => 'nullable|string',
            'modules' => 'nullable|array',
            'modules.*.id' => 'nullable|exists:course_modules,id',
            'modules.*.title' => 'nullable|string|max:255',
            'modules.*.description' => 'nullable|string|max:1000',
            'modules.*.order' => 'nullable|integer|min:0|max:999',
            'modules.*._destroy' => 'nullable|boolean',
            'classes' => 'nullable|array',
            'classes.*.id' => 'nullable|exists:classes,id',
            'classes.*.name' => 'nullable|string|max:255',
            'classes.*.instructor_id' => 'nullable|exists:users,id',
            'classes.*.start_date' => 'nullable|date',
            'classes.*.end_date' => 'nullable|date',
            'classes.*.schedule' => 'nullable|string',
            'classes.*.meeting_info' => 'nullable|string',
            'classes.*.max_students' => 'nullable|integer|min:0',
            'classes.*.price_override' => 'nullable|numeric|min:0',
            'classes.*.status' => 'nullable|in:active,inactive',
            'classes.*._destroy' => 'nullable|boolean',
        ];
    }

    protected function extractCourseData(Request $request, array $validated, ?Course $course = null): array
    {
        if ($request->hasFile('thumbnail')) {
            if ($course?->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        if ($request->hasFile('banner_image')) {
            if ($course?->banner_image) {
                Storage::disk('public')->delete($course->banner_image);
            }
            $validated['banner_image'] = $request->file('banner_image')->store('courses/banners', 'public');
        }

        if ($request->hasFile('pdf')) {
            if ($course?->pdf_path) {
                Storage::disk('public')->delete($course->pdf_path);
            }
            $validated['pdf_path'] = $request->file('pdf')->store('courses/pdfs', 'public');
        }

        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_popular'] = $request->boolean('is_popular');
        $validated['learning_type'] = ($validated['learning_type'] ?? 'online') === 'online' ? 'online' : 'offline';

        unset($validated['classes'], $validated['modules'], $validated['pdf']);

        return $validated;
    }

    protected function syncModules(Course $course, array $modules): void
    {
        foreach ($modules as $index => $moduleData) {
            $moduleId = $moduleData['id'] ?? null;
            $title = trim((string) ($moduleData['title'] ?? ''));
            $description = trim((string) ($moduleData['description'] ?? ''));
            $order = isset($moduleData['order']) && $moduleData['order'] !== ''
                ? (int) $moduleData['order']
                : ($index + 1);

            if ($moduleId) {
                $module = $course->modules()->whereKey($moduleId)->first();
                if (! $module) {
                    continue;
                }

                if (! empty($moduleData['_destroy'])) {
                    $module->materials()->update(['course_module_id' => null]);
                    $module->delete();
                    continue;
                }

                if ($title === '') {
                    continue;
                }

                $module->update([
                    'title' => $title,
                    'description' => $description ?: null,
                    'order' => $order,
                ]);

                continue;
            }

            if ($title === '' || ! empty($moduleData['_destroy'])) {
                continue;
            }

            $course->modules()->create([
                'title' => $title,
                'description' => $description ?: null,
                'order' => $order,
            ]);
        }
    }

    protected function syncClasses(Course $course, array $classes): void
    {
        foreach ($classes as $classData) {
            $classId = $classData['id'] ?? null;
            $name = trim((string) ($classData['name'] ?? ''));
            $payload = [
                'name' => $name,
                'instructor_id' => $classData['instructor_id'] ?? null,
                'start_date' => $classData['start_date'] ?? null,
                'end_date' => $classData['end_date'] ?? null,
                'schedule' => $classData['schedule'] ?? null,
                'meeting_info' => $classData['meeting_info'] ?? null,
                'max_students' => $classData['max_students'] ?? 0,
                'price_override' => $classData['price_override'] ?? null,
                'status' => $classData['status'] ?? 'active',
            ];

            if ($classId) {
                $class = $course->classes()->whereKey($classId)->first();
                if (! $class) {
                    continue;
                }

                if (! empty($classData['_destroy'])) {
                    if ($class->enrollments()->exists()) {
                        $class->update(['status' => 'inactive']);
                    } else {
                        $class->delete();
                    }
                    continue;
                }

                if ($name === '' || ! $this->hasEnoughClassData($payload)) {
                    continue;
                }

                $class->update($payload);
                continue;
            }

            if ($name === '' || ! $this->hasEnoughClassData($payload) || ! empty($classData['_destroy'])) {
                continue;
            }

            $course->classes()->create($payload);
        }
    }

    protected function hasEnoughClassData(array $payload): bool
    {
        return filled($payload['name'])
            && filled($payload['instructor_id'])
            && filled($payload['start_date'])
            && filled($payload['end_date']);
    }

    protected function syncPrimaryInstructorFromClasses(Course $course): void
    {
        if ($course->instructor_id) {
            return;
        }

        $primaryClass = $course->classes()
            ->get()
            ->sort(function (CourseClass $left, CourseClass $right): int {
                $leftPriority = $left->status === 'active' ? 0 : 1;
                $rightPriority = $right->status === 'active' ? 0 : 1;

                if ($leftPriority !== $rightPriority) {
                    return $leftPriority <=> $rightPriority;
                }

                $leftStart = $left->start_date?->format('Y-m-d') ?? '9999-12-31';
                $rightStart = $right->start_date?->format('Y-m-d') ?? '9999-12-31';

                if ($leftStart !== $rightStart) {
                    return $leftStart <=> $rightStart;
                }

                return ((int) $left->id) <=> ((int) $right->id);
            })
            ->first();

        if ($primaryClass?->instructor_id) {
            $course->update(['instructor_id' => $primaryClass->instructor_id]);
        }
    }

    public function destroy(Course $course)
    {
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }
        if ($course->banner_image) {
            Storage::disk('public')->delete($course->banner_image);
        }
        if ($course->pdf_path) {
            Storage::disk('public')->delete($course->pdf_path);
        }

        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Khóa học đã được xóa thành công.');
    }

    public function toggleFeatured(Course $course)
    {
        $course->update(['is_featured' => ! $course->is_featured]);
        $message = $course->is_featured ? 'Khóa học đã được đánh dấu nổi bật.' : 'Khóa học đã bỏ đánh dấu nổi bật.';

        return back()->with('success', $message);
    }

    public function togglePopular(Course $course)
    {
        $course->update(['is_popular' => ! $course->is_popular]);
        $message = $course->is_popular ? 'Khóa học đã được đánh dấu phổ biến.' : 'Khóa học đã bỏ đánh dấu phổ biến.';

        return back()->with('success', $message);
    }
}
