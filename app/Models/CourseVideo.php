<?php

namespace App\Models;

use App\Models\MongoModel as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;

class CourseVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'course_id',
        'lesson_id',
        'original_filename',
        'video_path',
        'hls_playlist_path',
        'hls_segments_path',
        'duration',
        'video_codec',
        'width',
        'height',
        'file_size',
        'processing_status',
        'processing_error',
        'order',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson()
    {
        return $this->belongsTo(CourseMaterial::class, 'lesson_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeProcessed($query)
    {
        return $query->where('processing_status', 'completed');
    }

    public function scopeByOrder($query)
    {
        return $query->orderBy('order');
    }

    // Accessors
    public function getVideoUrlAttribute()
    {
        return $this->hls_playlist_path ? Storage::url($this->hls_playlist_path) : null;
    }

    public function getOriginalVideoUrlAttribute()
    {
        return Storage::url($this->video_path);
    }

    public function getFormattedDurationAttribute()
    {
        if (!$this->duration) return 'N/A';

        $hours = floor($this->duration / 3600);
        $minutes = floor(($this->duration % 3600) / 60);
        $seconds = $this->duration % 60;

        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function getFileSizeFormattedAttribute()
    {
        if (!$this->file_size) return 'N/A';

        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = $this->file_size;
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    // Methods
    public function isProcessed()
    {
        return $this->processing_status === 'completed';
    }

    public function isProcessing()
    {
        return $this->processing_status === 'processing';
    }

    public function hasError()
    {
        return $this->processing_status === 'failed';
    }

    public function markAsProcessing()
    {
        $this->processing_status = 'processing';
        $this->processing_error = null;
        $this->save();
    }

    public function markAsCompleted($playlistPath = null, $segmentsPath = null)
    {
        $this->processing_status = 'completed';
        $this->processing_error = null;

        if ($playlistPath) {
            $this->hls_playlist_path = $playlistPath;
        }

        if ($segmentsPath) {
            $this->hls_segments_path = $segmentsPath;
        }

        $this->save();
    }

    public function markAsFailed($error)
    {
        $this->processing_status = 'failed';
        $this->processing_error = $error;
        $this->save();
    }

    public function getHlsSegments()
    {
        if (!$this->hls_segments_path) return [];

        $segments = [];
        $path = storage_path('app/public/' . $this->hls_segments_path);

        if (is_dir($path)) {
            $files = glob($path . '/*.ts');
            foreach ($files as $file) {
                $segments[] = basename($file);
            }
        }

        return $segments;
    }
}
