<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourseVideo;
use App\Models\Course;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function index()
    {
        $instructorId = auth()->id();

        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        $videos = CourseVideo::with('course')
            ->whereIn('course_id', $courseIds)
            ->latest()
            ->paginate(12);

        return view('instructor.videos.index', compact('videos'));
    }

    public function create()
    {
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();
        $courses = Course::whereIn('id', $courseIds)->get();

        return view('instructor.videos.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'video_file' => 'required|file|mimes:mp4,mov,avi,wmv|max:102400',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($request->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $file = $request->file('video_file');
        $originalFilename = $file->getClientOriginalName();
        $fileSize = $file->getSize();

        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('videos', $filename, 'public');

        CourseVideo::create([
            'course_id' => $request->course_id,
            'title' => $request->title,
            'description' => $request->description,
            'original_filename' => $originalFilename,
            'video_path' => $path,
            'file_size' => $fileSize,
            'processing_status' => 'pending',
            'order' => $request->order ?? 0,
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->route('instructor.videos.index')
            ->with('success', 'Video đã được upload và đang được xử lý!');
    }

    public function show(CourseVideo $video)
    {
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($video->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        return view('instructor.videos.show', compact('video'));
    }

    public function edit(CourseVideo $video)
    {
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($video->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $courses = Course::whereIn('id', $courseIds)->get();
        return view('instructor.videos.edit', compact('video', 'courses'));
    }

    public function update(Request $request, CourseVideo $video)
    {
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($video->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // Check new course ownership
        if (!in_array($request->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        $video->update([
            'title' => $request->title,
            'description' => $request->description,
            'course_id' => $request->course_id,
            'order' => $request->order ?? $video->order,
            'is_active' => $request->is_active ?? $video->is_active,
        ]);

        return redirect()->route('instructor.videos.index')
            ->with('success', 'Video đã được cập nhật thành công!');
    }

    public function destroy(CourseVideo $video)
    {
        $instructorId = auth()->id();
        $classIds = \App\Models\CourseClass::where('instructor_id', $instructorId)->pluck('id');
        $courseIds = \App\Models\CourseClass::whereIn('id', $classIds)->pluck('course_id')->unique();

        if (!in_array($video->course_id, $courseIds->toArray()) && !auth()->user()->isAdmin()) {
            abort(403);
        }

        if ($video->video_path && Storage::disk('public')->exists($video->video_path)) {
            Storage::disk('public')->delete($video->video_path);
        }

        if ($video->hls_segments_path) {
            $hlsPath = dirname($video->video_path) . '/' . $video->hls_segments_path;
            if (Storage::disk('public')->exists($hlsPath)) {
                Storage::disk('public')->deleteDirectory($hlsPath);
            }
        }

        $video->delete();

        return redirect()->route('instructor.videos.index')
            ->with('success', 'Video đã được xóa thành công!');
    }

    public function getFileSizeFormatted(CourseVideo $video)
    {
        $bytes = $video->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getStatusBadgeClass(CourseVideo $video)
    {
        return match($video->processing_status) {
            'completed' => 'bg-success',
            'processing' => 'bg-warning',
            'failed' => 'bg-danger',
            default => 'bg-secondary',
        };
    }

    public function getStatusText(CourseVideo $video)
    {
        return match($video->processing_status) {
            'pending' => 'Chờ xử lý',
            'processing' => 'Đang xử lý',
            'completed' => 'Hoàn thành',
            'failed' => 'Lỗi',
            default => 'Không xác định',
        };
    }
}