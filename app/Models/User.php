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
        'otp_sent_at',
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
        'otp_sent_at' => 'datetime',
        'rating' => 'decimal:1',
        'total_rating' => 'integer',
    ];

    public function isAdmin(): bool
    {
        return $this->roleKey() === 'admin';
    }

    public function isInstructor(): bool
    {
        return $this->roleKey() === 'instructor';
    }

    public function isStudent(): bool
    {
        return $this->roleKey() === 'student';
    }

    public function scopeAdmins($query)
    {
        return $query->whereIn('role', ['admin', 'staff']);
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

    public function roleKey(): string
    {
        return $this->role === 'staff' ? 'admin' : (string) $this->role;
    }

    public function roleLabel(): string
    {
        return match ($this->roleKey()) {
            'admin' => 'Quản trị',
            'instructor' => 'Giảng viên',
            default => 'Học viên',
        };
    }

    public function roleBadgeClass(): string
    {
        return match ($this->roleKey()) {
            'admin' => 'danger',
            'instructor' => 'primary',
            default => 'info',
        };
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
        return $this->wallet()->firstOrCreate([], [
            'balance' => 0,
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
        $ratings = $this->instructorReviews()->pluck('instructor_rating');
        $this->rating = $ratings->isEmpty() ? 0 : $ratings->avg();
        $this->total_rating = $ratings->count();
        $this->save();
    }

    public function buildStudentLevelSummary(): array
    {
        return StudentLevel::makeForUser($this);
    }
}
