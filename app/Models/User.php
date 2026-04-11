<?php

namespace App\Models;

use App\Support\StudentLevel;
use App\Models\MongoAuthenticatable as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'username',
        'fullname',
        'email',
        'password',
        'avatar',
        'otp',
        'is_verified',
        'role',
        'google_id',
        'facebook_id',
        'provider',
        'provider_id',
        'rating',
        'total_rating',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'email_verified_at' => 'datetime',
        'rating' => 'decimal:1',
        'total_rating' => 'integer',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function isInstructor(): bool
    {
        return $this->role === 'instructor';
    }

    public function isStudent(): bool
    {
        return $this->role === 'student';
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeStaff($query)
    {
        return $query->where('role', 'staff');
    }

    public function scopeInstructors($query)
    {
        return $query->where('role', 'instructor');
    }

    public function scopeStudents($query)
    {
        return $query->where('role', 'student');
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function certificates()
    {
        return $this->hasMany(CourseCertificate::class);
    }

    public function materialProgress()
    {
        return $this->hasMany(CourseMaterialProgress::class);
    }

    public function portalNotifications()
    {
        return $this->morphMany(AppNotification::class, 'notifiable');
    }

    public function unreadPortalNotifications()
    {
        return $this->portalNotifications()->unread();
    }

    public function getBalanceAttribute()
    {
        return $this->wallet?->balance ?? 0;
    }

    public function getOrCreateWallet(): Wallet
    {
        return $this->wallet()->firstOrCreate([
            'firefly_identity' => 'user:' . $this->id,
        ]);
    }

    /**
     * Reviews submitted by this user (as student).
     */
    public function reviews()
    {
        return $this->hasMany(CourseReview::class);
    }

    /**
     * Reviews received by this user as an instructor.
     */
    public function instructorReviews()
    {
        return $this->hasMany(CourseReview::class, 'instructor_id');
    }

    public function updateRating()
    {
        $reviews = $this->instructorReviews();
        $this->rating = $reviews->avg('instructor_rating') ?? 0;
        $this->total_rating = $reviews->count();
        $this->save();
    }

    public function buildStudentLevelSummary(): array
    {
        return StudentLevel::makeForUser($this);
    }
}
