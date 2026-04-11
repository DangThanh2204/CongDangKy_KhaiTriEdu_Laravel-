<?php

namespace App\Models;

use App\Models\MongoModel as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'attempt_id',
        'question_id',
        'user_answer',
        'is_correct',
        'points_earned',
    ];

    protected $casts = [
        'user_answer' => 'array',
        'is_correct' => 'boolean',
    ];

    // Relationships
    public function attempt()
    {
        return $this->belongsTo(QuizAttempt::class, 'attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(QuizQuestion::class, 'question_id');
    }

    // Methods
    public function evaluate()
    {
        $this->is_correct = $this->question->isCorrect($this->user_answer);
        $this->points_earned = $this->is_correct ? $this->question->points : 0;
        $this->save();

        return $this->is_correct;
    }
}
