<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'otp',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    // Phương thức kiểm tra role
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

    // Scope để lọc theo role
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
}