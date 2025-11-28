<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCourseCategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = CourseCategory::withCount('courses');

        // Search
        if ($request->has('search') && $request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Filter by status
        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $categories = $query->ordered()->paginate(10);

        $stats = [
            'totalCategories' => CourseCategory::count(),
            'activeCategories' => CourseCategory::where('is_active', true)->count(),
            'inactiveCategories' => CourseCategory::where('is_active', false)->count(),
        ];

        return view('admin.course-categories.index', compact('categories', 'stats'));
    }

    public function create()
    {
        $parentCategories = CourseCategory::whereNull('parent_id')->active()->get();
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
        $validated['is_active'] = $request->has('is_active');

        CourseCategory::create($validated);

        return redirect()->route('admin.course-categories.index')
            ->with('success', 'Danh mục khóa học đã được tạo thành công!');
    }

    public function edit(CourseCategory $courseCategory)
    {
        $parentCategories = CourseCategory::whereNull('parent_id')
            ->where('id', '!=', $courseCategory->id)
            ->active()
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

        $validated['is_active'] = $request->has('is_active');

        $courseCategory->update($validated);

        return redirect()->route('admin.course-categories.index')
            ->with('success', 'Danh mục khóa học đã được cập nhật thành công!');
    }

    public function destroy(CourseCategory $courseCategory)
    {
        // Check if category has courses
        if ($courseCategory->courses()->exists()) {
            return back()->with('error', 'Không thể xóa danh mục đang chứa khóa học!');
        }

        // Check if category has children
        if ($courseCategory->children()->exists()) {
            return back()->with('error', 'Không thể xóa danh mục đang chứa danh mục con!');
        }

        $courseCategory->delete();

        return back()->with('success', 'Danh mục khóa học đã được xóa thành công!');
    }

    public function toggleStatus(CourseCategory $courseCategory)
    {
        $courseCategory->update(['is_active' => !$courseCategory->is_active]);

        $message = $courseCategory->is_active ? 'Danh mục đã được kích hoạt!' : 'Danh mục đã bị vô hiệu hóa!';

        return back()->with('success', $message);
    }
}