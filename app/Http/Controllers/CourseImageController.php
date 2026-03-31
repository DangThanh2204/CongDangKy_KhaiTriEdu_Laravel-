<?php

namespace App\Http\Controllers;

use App\Models\Course;
use Illuminate\Http\Request;

class CourseImageController extends Controller
{
    /**
     * Serve course image (banner or thumbnail).
     * Supports stored file paths, absolute URLs, data URI, or raw BLOB stored in DB.
     */
    public function show(Course $course, $type)
    {
        $field = null;
        if (in_array($type, ['banner', 'thumbnail'])) {
            $field = $type === 'banner' ? 'banner_image' : 'thumbnail';
        }

        if (!$field) {
            abort(404);
        }

        $value = $course->{$field};

        // Empty -> fallback image
        if (!$value) {
            $default = $type === 'banner' ? public_path('images/default-banner.jpg') : public_path('images/default-course.jpg');
            if (file_exists($default)) {
                return response()->file($default);
            }
            abort(404);
        }

        // If it's a full URL, redirect
        if (preg_match('#^https?://#i', $value)) {
            return redirect($value);
        }

        // If it's a data URI (base64), decode and return
        if (preg_match('#^data:(image/[^;]+);base64,(.+)$#i', $value, $m)) {
            $mime = $m[1];
            $data = base64_decode($m[2]);
            return response($data, 200)->header('Content-Type', $mime);
        }

        // If the value looks like a storage path or filesystem path
        $clean = ltrim($value, '/');
        // Check public storage first
        $storagePath = public_path('storage/' . $clean);
        if (file_exists($storagePath)) {
            return response()->file($storagePath);
        }

        // Check public path directly
        $publicPath = public_path($clean);
        if (file_exists($publicPath)) {
            return response()->file($publicPath);
        }

        // Otherwise value might be raw binary stored in DB
        // Try to detect mime type from content
        if (!empty($value)) {
            $data = $value;
            // If value is base64 (without data: prefix), try to decode safely
            if (preg_match('/^[a-zA-Z0-9\/+=\s]+$/', $value) && strlen($value) > 100 && strpos($value, '\n') !== false) {
                $maybe = @base64_decode($value, true);
                if ($maybe !== false) {
                    $data = $maybe;
                }
            }

            if (!empty($data)) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->buffer($data) ?: 'application/octet-stream';
                return response($data, 200)->header('Content-Type', $mime);
            }
        }

        abort(404);
    }
}
