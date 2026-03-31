<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassChangeLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'user_id',
        'old_class_id',
        'new_class_id',
        'reason',
    ];

    public function enrollment()
    {
        return $this->belongsTo(CourseEnrollment::class, 'enrollment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
