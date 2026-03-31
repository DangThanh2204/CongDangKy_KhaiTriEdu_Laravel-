<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'class_id',
        'status',
        'enrolled_at',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function student()
    {
        return $this->user();
    }

    public function class()
    {
        return $this->belongsTo(CourseClass::class, 'class_id');
    }

    public function courseClass()
    {
        return $this->class();
    }

    public function course()
    {
        return $this->hasOneThrough(
            Course::class,
            CourseClass::class,
            'id',
            'id',
            'class_id',
            'course_id'
        );
    }

    public function materialProgress()
    {
        return $this->hasMany(CourseMaterialProgress::class, 'enrollment_id');
    }

    public function quizAttempts()
    {
        return $this->hasMany(CourseMaterialQuizAttempt::class, 'enrollment_id');
    }

    public function certificate()
    {
        return $this->hasOne(CourseCertificate::class, 'enrollment_id');
    }

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

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeCompleted($query)
    {
        return $query->where(function ($innerQuery) {
            $innerQuery->where('status', 'completed')
                ->orWhereNotNull('completed_at');
        });
    }

    public function scopeForCourse($query, $course)
    {
        $courseId = $course instanceof Course ? $course->id : $course;

        return $query->whereHas('courseClass', function ($classQuery) use ($courseId) {
            $classQuery->where('course_id', $courseId);
        });
    }

    public function approve(): void
    {
        $shouldIncreaseCount = ! in_array($this->status, ['approved', 'completed'], true);

        $this->forceFill([
            'status' => 'approved',
            'enrolled_at' => $this->enrolled_at ?: now(),
            'approved_at' => now(),
            'rejected_at' => null,
            'cancelled_at' => null,
        ])->save();

        if ($shouldIncreaseCount) {
            $this->adjustCourseStudentsCount(1);
        }
    }

    public function reject($notes = null): void
    {
        $shouldDecreaseCount = in_array($this->status, ['approved', 'completed'], true);

        $payload = [
            'status' => 'rejected',
            'rejected_at' => now(),
            'cancelled_at' => null,
            'completed_at' => null,
        ];

        if ($notes !== null) {
            $payload['notes'] = $notes;
        }

        $this->forceFill($payload)->save();

        if ($shouldDecreaseCount) {
            $this->adjustCourseStudentsCount(-1);
        }
    }

    public function cancel($notes = null): void
    {
        $shouldDecreaseCount = in_array($this->status, ['approved', 'completed'], true);

        $payload = [
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ];

        if ($notes !== null) {
            $payload['notes'] = $notes;
        }

        $this->forceFill($payload)->save();

        if ($shouldDecreaseCount) {
            $this->adjustCourseStudentsCount(-1);
        }
    }

    public function complete(): void
    {
        $this->forceFill([
            'status' => 'completed',
            'enrolled_at' => $this->enrolled_at ?: now(),
            'approved_at' => $this->approved_at ?: ($this->enrolled_at ?: now()),
            'completed_at' => now(),
        ])->save();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isCompleted(): bool
    {
        return ! is_null($this->completed_at) || $this->status === 'completed';
    }

    public function getDeliveryModeAttribute(): string
    {
        $this->loadMissing('courseClass.course');

        return $this->courseClass?->course?->delivery_mode ?? 'online';
    }

    public function getStatusTextAttribute()
    {
        if ($this->isCompleted()) {
            return 'Hoàn thành';
        }

        $statuses = [
            'pending' => 'Chờ duyệt',
            'approved' => 'Đã duyệt',
            'rejected' => 'Từ chối',
            'cancelled' => 'Đã hủy',
            'completed' => 'Hoàn thành',
        ];

        return $statuses[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute()
    {
        if ($this->isCompleted()) {
            return 'info';
        }

        $colors = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
            'completed' => 'info',
        ];

        return $colors[$this->status] ?? 'secondary';
    }

    private function adjustCourseStudentsCount(int $delta): void
    {
        $this->loadMissing('courseClass.course');

        $course = $this->courseClass?->course;

        if (! $course || $delta === 0) {
            return;
        }

        if ($delta > 0) {
            $course->increment('students_count', $delta);
            return;
        }

        $course->decrement('students_count', abs($delta));
    }
}