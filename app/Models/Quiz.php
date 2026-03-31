<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'course_id',
        'lesson_id',
        'type',
        'time_limit',
        'passing_score',
        'max_attempts',
        'is_active',
        'shuffle_questions',
        'show_results',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'shuffle_questions' => 'boolean',
        'show_results' => 'boolean',
        'settings' => 'array',
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

    public function questions()
    {
        return $this->hasMany(QuizQuestion::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Methods
    public function getTotalQuestions()
    {
        return $this->questions()->active()->count();
    }

    public function getTotalPoints()
    {
        return $this->questions()->active()->sum('points');
    }

    public function canUserAttempt($userId)
    {
        $attemptsCount = $this->attempts()->where('user_id', $userId)->count();
        return $attemptsCount < $this->max_attempts;
    }

    public function getUserBestScore($userId)
    {
        return $this->attempts()
            ->where('user_id', $userId)
            ->where('status', 'completed')
            ->max('percentage_score') ?? 0;
    }

    public function isPassed($score)
    {
        return $score >= $this->passing_score;
    }
}
