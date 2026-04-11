<?php

namespace App\Models;

use App\Models\MongoModel as Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseReviewReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'review_id',
        'user_id',
        'comment',
    ];

    public function review()
    {
        return $this->belongsTo(CourseReview::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
