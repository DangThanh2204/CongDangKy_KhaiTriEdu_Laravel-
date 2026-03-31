<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseMaterialProgress extends Model
{
    use HasFactory;

    protected $table = 'course_material_progress';

    protected $fillable = [
        'enrollment_id',
        'course_material_id',
        'user_id',
        'progress_percent',
        'started_at',
        'last_viewed_at',
        'completed_at',
        'best_quiz_score',
        'quiz_attempts_count',
        'passed_at',
        'meta',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_viewed_at' => 'datetime',
        'completed_at' => 'datetime',
        'passed_at' => 'datetime',
        'best_quiz_score' => 'decimal:2',
        'meta' => 'array',
    ];

    public function enrollment()
    {
        return $this->belongsTo(CourseEnrollment::class, 'enrollment_id');
    }

    public function material()
    {
        return $this->belongsTo(CourseMaterial::class, 'course_material_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isCompleted(): bool
    {
        return ! is_null($this->completed_at);
    }

    public function isPassed(): bool
    {
        return ! is_null($this->passed_at);
    }
}
