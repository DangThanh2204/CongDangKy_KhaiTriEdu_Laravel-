<?php

namespace App\Models;

use App\Models\MongoModel as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question_text',
        'question_type',
        'options',
        'correct_answers',
        'explanation',
        'points',
        'order',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function answers()
    {
        return $this->hasMany(QuizAnswer::class, 'question_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Methods
    public function isCorrect($userAnswer)
    {
        $correctAnswers = $this->correct_answers ?? [];

        if ($this->question_type === 'multiple_choice' || $this->question_type === 'true_false') {
            // For multiple choice and true/false, compare arrays
            sort($userAnswer);
            sort($correctAnswers);
            return $userAnswer === $correctAnswers;
        } elseif ($this->question_type === 'short_answer') {
            // For short answer, case-insensitive comparison
            return strtolower(trim($userAnswer[0] ?? '')) === strtolower(trim($correctAnswers[0] ?? ''));
        }

        return false;
    }

    public function getOptionsArray()
    {
        return $this->options ?? [];
    }

    public function getCorrectAnswersText()
    {
        if ($this->question_type === 'multiple_choice' && $this->options) {
            $options = $this->getOptionsArray();
            $correctIndices = $this->correct_answers ?? [];

            return collect($correctIndices)->map(function($index) use ($options) {
                return $options[$index] ?? 'N/A';
            })->join(', ');
        }

        return implode(', ', $this->correct_answers ?? []);
    }
}
