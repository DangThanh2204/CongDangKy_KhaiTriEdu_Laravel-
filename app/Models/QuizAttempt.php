<?php

namespace App\Models;

use App\Models\MongoModel as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'attempt_number',
        'total_questions',
        'correct_answers',
        'total_points',
        'earned_points',
        'percentage_score',
        'status',
        'started_at',
        'completed_at',
        'time_taken',
        'answers_summary',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'answers_summary' => 'array',
        'percentage_score' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'attempt_id');
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    // Methods
    public function calculateScore()
    {
        $totalPoints = $this->answers()->sum('points_earned');
        $maxPoints = $this->total_points;

        if ($maxPoints > 0) {
            $this->earned_points = $totalPoints;
            $this->percentage_score = round(($totalPoints / $maxPoints) * 100, 2);
            $this->correct_answers = $this->answers()->where('is_correct', true)->count();
        }

        $this->save();
        return $this->percentage_score;
    }

    public function complete()
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->time_taken = $this->started_at->diffInSeconds(now());
        $this->calculateScore();
        $this->save();
    }

    public function isPassed()
    {
        return $this->quiz->isPassed($this->percentage_score);
    }

    public function getTimeRemaining()
    {
        if (!$this->quiz->time_limit || $this->status !== 'in_progress') {
            return null;
        }

        $elapsed = $this->started_at->diffInSeconds(now());
        $remaining = ($this->quiz->time_limit * 60) - $elapsed;

        return max(0, $remaining);
    }
}
