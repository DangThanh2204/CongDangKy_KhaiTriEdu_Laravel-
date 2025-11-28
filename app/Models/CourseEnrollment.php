<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'status',
        'notes',
        'approved_at',
        'rejected_at',
        'requires_approval',
        'enrolled_at',
        'completed_at'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'requires_approval' => 'boolean'
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Methods
    public function approve()
    {
        $this->update([
            'status' => 'approved',
            'approved_at' => now(),
            'rejected_at' => null,
            'enrolled_at' => now()
        ]);

        // Cập nhật số lượng học viên
        $this->course->increment('students_count');
    }

    public function reject($notes = null)
    {
        $this->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'approved_at' => null,
            'notes' => $notes
        ]);
    }

    public function complete()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now()
        ]);
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function requiresApproval()
    {
        return $this->requires_approval;
    }

    // Accessors
    public function getStatusTextAttribute()
    {
        $statuses = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            'completed' => 'Hoàn thành'
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        $colors = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'completed' => 'info'
        ];

        return $colors[$this->status] ?? 'secondary';
    }
}