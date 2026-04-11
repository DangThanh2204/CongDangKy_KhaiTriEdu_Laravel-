<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MongoModel as Model;

class CourseMaterialQuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'course_material_id',
        'user_id',
        'attempt_number',
        'total_questions',
        'correct_answers',
        'score_percent',
        'is_passed',
        'answers_summary',
        'completed_at',
    ];

    protected $casts = [
        'score_percent' => 'decimal:2',
        'is_passed' => 'boolean',
        'answers_summary' => 'array',
        'completed_at' => 'datetime',
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
}
