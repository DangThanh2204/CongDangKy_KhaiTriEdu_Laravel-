<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CourseVideo;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminVideoController extends Controller
{
    public function index()
    {
        $videos = CourseVideo::with('course')->paginate(15);
        return view('admin.videos.index', compact('videos'));
    }

    public function create()
    {
        $courses = Course::published()->get();
        return view('admin.videos.create', compact('courses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'video_file' => 'required|file|mimes:mp4,mov,avi,wmv|max:102400', // 100MB
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|exists:course_materials,id',
        ]);

        $file = $request->file('video_file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('videos/original', $filename, 'public');

        $video = CourseVideo::create([
            'title' => $request->title,
            'description' => $request->description,
            'course_id' => $request->course_id,
            'lesson_id' => $request->lesson_id,
            'original_filename' => $file->getClientOriginalName(),
            'video_path' => $path,
            'file_size' => $file->getSize(),
            'order' => $request->order ?? 0,
        ]);

        // Dispatch job để process video
        // ProcessVideoJob::dispatch($video);

        return redirect()->route('admin.videos.index')
            ->with('success', 'Video uploaded successfully. Processing will begin shortly.');
    }

    public function show(CourseVideo $video)
    {
        return view('admin.videos.show', compact('video'));
    }

    public function edit(CourseVideo $video)
    {
        $courses = Course::published()->get();
        return view('admin.videos.edit', compact('video', 'courses'));
    }

    public function update(Request $request, CourseVideo $video)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'nullable|exists:course_materials,id',
            'order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        $video->update($validated);
        return redirect()->route('admin.videos.index')->with('success', 'Video updated successfully');
    }

    public function destroy(CourseVideo $video)
    {
        // Delete files
        if ($video->video_path && Storage::disk('public')->exists($video->video_path)) {
            Storage::disk('public')->delete($video->video_path);
        }

        if ($video->hls_playlist_path && Storage::disk('public')->exists($video->hls_playlist_path)) {
            Storage::disk('public')->delete($video->hls_playlist_path);
        }

        if ($video->hls_segments_path) {
            $segmentsPath = storage_path('app/public/' . $video->hls_segments_path);
            if (is_dir($segmentsPath)) {
                $files = glob($segmentsPath . '/*');
                foreach ($files as $file) {
                    if (is_file($file)) {
                        unlink($file);
                    }
                }
                rmdir($segmentsPath);
            }
        }

        $video->delete();
        return redirect()->route('admin.videos.index')->with('success', 'Video deleted successfully');
    }

    public function process(CourseVideo $video)
    {
        if ($video->processing_status !== 'pending') {
            return response()->json(['message' => 'Video is already being processed'], 400);
        }

        // ProcessVideoJob::dispatch($video);

        return response()->json(['message' => 'Video processing started']);
    }

    public function stream(CourseVideo $video)
    {
        if (!$video->isProcessed()) {
            abort(404, 'Video not available');
        }

        $playlistPath = storage_path('app/public/' . $video->hls_playlist_path);

        if (!file_exists($playlistPath)) {
            abort(404, 'Playlist not found');
        }

        return response()->file($playlistPath, [
            'Content-Type' => 'application/x-mpegURL',
        ]);
    }
}
