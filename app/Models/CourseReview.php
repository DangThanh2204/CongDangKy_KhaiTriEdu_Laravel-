<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'user_id',
        'instructor_id',
        'rating',
        'instructor_rating',
        'comment',
    ];

    // Relationships
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function replies()
    {
        return $this->hasMany(CourseReviewReply::class, 'review_id')->orderBy('created_at');
    }
}
