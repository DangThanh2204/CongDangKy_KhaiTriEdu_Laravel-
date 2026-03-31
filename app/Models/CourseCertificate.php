<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseCertificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'enrollment_id',
        'user_id',
        'certificate_no',
        'issued_at',
        'meta',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'meta' => 'array',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment()
    {
        return $this->belongsTo(CourseEnrollment::class, 'enrollment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
