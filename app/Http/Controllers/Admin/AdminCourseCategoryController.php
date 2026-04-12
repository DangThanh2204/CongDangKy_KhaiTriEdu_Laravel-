<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class AdminCourseCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseCategory::query()->with('parent');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $categories = $query->ordered()->paginate(10)->withQueryString();
        $this->attachCourseCounts($categories->getCollection());

        $stats = [
            'totalCategories' => CourseCategory::count(),
            'activeCategories' => CourseCategory::where('is_active', true)->count(),
            'inactiveCategories' => CourseCategory::where('is_active', false)->count(),
            'totalCourses' => Course::count(),
        ];

        return view('admin.course-categories.index', compact('categories', 'stats'));
    }

    public function create()
    {
        $parentCategories = CourseCategory::whereNull('parent_id')
            ->active()
            ->ordered()
            ->get();

        return view('admin.course-categories.create', compact('parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:course_categories,name',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:course_categories,id',
            'icon' => 'nullable|string|max:50',
            'color' => 'required|string|max:7',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_active'] = $request->boolean('is_active');

        CourseCategory::create($validated);

        return redirect()->route('admin.course-categories.index')
            ->with('success', 'Nhóm ngành đã được tạo thành công!');
    }

    public function edit(CourseCategory $courseCategory)
    {
        $this->attachCourseCounts(collect([$courseCategory]));

        $parentCategories = CourseCategory::whereNull('parent_id')
            ->where('id', '!=', $courseCategory->id)
            ->active()
            ->ordered()
            ->get();

        return view('admin.course-categories.edit', compact('courseCategory', 'parentCategories'));
    }

    public function update(Request $request, CourseCategory $courseCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:course_categories,name,' . $courseCategory->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:course_categories,id',
            'icon' => 'nullable|string|max:50',
            'color' => 'required|string|max:7',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        if ($courseCategory->name !== $validated['name']) {
            $validated['slug'] = Str::slug($validated['name']);
        }

        $validated['is_active'] = $request->boolean('is_active');

        $courseCategory->update($validated);

        return redirect()->route('admin.course-categories.index')
            ->with('success', 'Nhóm ngành đã được cập nhật thành công!');
    }

    public function destroy(CourseCategory $courseCategory)
    {
        if ($courseCategory->courses()->exists()) {
            return back()->with('error', 'Không thể xóa nhóm ngành đang chứa khóa học!');
        }

        if ($courseCategory->children()->exists()) {
            return back()->with('error', 'Không thể xóa nhóm ngành đang chứa nhóm ngành con!');
        }

        $courseCategory->delete();

        return back()->with('success', 'Nhóm ngành đã được xóa thành công!');
    }

    public function toggleStatus(CourseCategory $courseCategory)
    {
        $courseCategory->update(['is_active' => ! $courseCategory->is_active]);

        $message = $courseCategory->is_active
            ? 'Nhóm ngành đã được kích hoạt!'
            : 'Nhóm ngành đã bị vô hiệu hóa!';

        return back()->with('success', $message);
    }

    private function attachCourseCounts(Collection $categories): void
    {
        if ($categories->isEmpty()) {
            return;
        }

        $countsByCategory = Course::query()
            ->whereIn('category_id', $categories->pluck('id')->all())
            ->get(['category_id'])
            ->countBy(fn ($course) => (string) $course->category_id);

        $categories->each(function (CourseCategory $category) use ($countsByCategory) {
            $category->setAttribute('courses_count', (int) ($countsByCategory[(string) $category->id] ?? 0));
        });
    }
}
