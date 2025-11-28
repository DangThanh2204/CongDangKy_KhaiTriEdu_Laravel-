<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AdminCourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['category', 'instructor']);

        if ($request->has('search') && $request->search) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        if ($request->has('instructor') && $request->instructor) {
            $query->where('instructor_id', $request->instructor);
        }

        $courses = $query->latest()->paginate(10);

        $stats = [
            'totalCourses' => Course::count(),
            'publishedCourses' => Course::where('status', 'published')->count(),
            'draftCourses' => Course::where('status', 'draft')->count(),
            'featuredCourses' => Course::where('is_featured', true)->count(),
        ];

        $categories = CourseCategory::active()->get();
        $instructors = User::whereIn('role', ['instructor'])->get(['id','fullname', 'email']);

        return view('admin.courses.index', compact('courses', 'stats', 'categories', 'instructors'));
    }

    public function create()
    {
        $categories = CourseCategory::active()->get();
        $instructors = User::whereIn('role', ['instructor'])->get(['id','fullname', 'email']);
        
        return view('admin.courses.create', compact('categories', 'instructors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'required|string|max:500',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'level' => 'required|in:beginner,intermediate,advanced,all',
            'duration' => 'required|integer|min:1',
            'category_id' => 'required|exists:course_categories,id',
            'instructor_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,published',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        
        if ($request->hasFile('thumbnail')) {
            $validated['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        if ($request->hasFile('banner_image')) {
            $validated['banner_image'] = $request->file('banner_image')->store('courses/banners', 'public');
        }

        $validated['lessons_count'] = 0;
        $validated['students_count'] = 0;
        $validated['rating'] = 0;
        $validated['total_rating'] = 0;

        $course = Course::create($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Khóa học đã được tạo thành công!');
    }

    public function edit(Course $course)
    {
        $categories = CourseCategory::active()->get();
        $instructors = User::whereIn('role', ['instructor'])->get(['id','fullname', 'email']);
        
        return view('admin.courses.edit', compact('course', 'categories', 'instructors'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'short_description' => 'required|string|max:500',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'level' => 'required|in:beginner,intermediate,advanced,all',
            'duration' => 'required|integer|min:1',
            'category_id' => 'required|exists:course_categories,id',
            'instructor_id' => 'required|exists:users,id',
            'status' => 'required|in:draft,published',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:5120',
            'is_featured' => 'boolean',
            'is_popular' => 'boolean',
        ]);

        if ($course->title !== $validated['title']) {
            $validated['slug'] = Str::slug($validated['title']);
        }

        if ($request->hasFile('thumbnail')) {
            if ($course->thumbnail) {
                Storage::disk('public')->delete($course->thumbnail);
            }
            $validated['thumbnail'] = $request->file('thumbnail')->store('courses/thumbnails', 'public');
        }

        if ($request->hasFile('banner_image')) {
            if ($course->banner_image) {
                Storage::disk('public')->delete($course->banner_image);
            }
            $validated['banner_image'] = $request->file('banner_image')->store('courses/banners', 'public');
        }

        $course->update($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Khóa học đã được cập nhật thành công!');
    }

    public function destroy(Course $course)
    {
        // Delete associated files
        if ($course->thumbnail) {
            Storage::disk('public')->delete($course->thumbnail);
        }
        if ($course->banner_image) {
            Storage::disk('public')->delete($course->banner_image);
        }

        $course->delete();

        return redirect()->route('admin.courses.index')
            ->with('success', 'Khóa học đã được xóa thành công!');
    }

    public function toggleFeatured(Course $course)
    {
        $course->update(['is_featured' => !$course->is_featured]);

        $message = $course->is_featured ? 'Khóa học đã được đánh dấu nổi bật!' : 'Khóa học đã bỏ đánh dấu nổi bật!';

        return back()->with('success', $message);
    }

    public function togglePopular(Course $course)
    {
        $course->update(['is_popular' => !$course->is_popular]);

        $message = $course->is_popular ? 'Khóa học đã được đánh dấu phổ biến!' : 'Khóa học đã bỏ đánh dấu phổ biến!';

        return back()->with('success', $message);
    }
}