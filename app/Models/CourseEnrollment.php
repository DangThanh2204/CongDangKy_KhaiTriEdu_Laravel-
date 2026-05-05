<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MongoModel as Model;

class CourseEnrollment extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (self $enrollment): void {
            if (! filled($enrollment->class_id) || filled($enrollment->course_id)) {
                return;
            }

            $enrollment->course_id = CourseClass::query()
                ->whereKey($enrollment->class_id)
                ->value('course_id');
        });
    }

    protected $fillable = [
        'user_id',
        'course_id',
        'class_id',
        'status',
        'enrolled_at',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'waitlist_joined_at',
        'waitlist_promoted_at',
        'seat_hold_expires_at',
        'base_price',
        'discount_amount',
        'final_price',
        'discount_code_id',
        'discount_snapshot',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'waitlist_joined_at' => 'datetime',
        'waitlist_promoted_at' => 'datetime',
        'seat_hold_expires_at' => 'datetime',
        'base_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'final_price' => 'decimal:2',
        'discount_snapshot' => 'array',
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
        return $this->belongsTo(Course::class, 'course_id');
    }

    public function discountCode()
    {
        return $this->belongsTo(DiscountCode::class, 'discount_code_id');
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

    public function scopeWaitlisted($query)
    {
        return $query->whereNotNull('waitlist_joined_at')
            ->whereNull('waitlist_promoted_at');
    }

    public function scopeHoldingSeat($query)
    {
        return $query->whereNotNull('waitlist_promoted_at')
            ->whereNotNull('seat_hold_expires_at')
            ->where('seat_hold_expires_at', '>', now())
            ->where('status', 'pending');
    }

    public function scopeForCourse($query, $course)
    {
        $courseId = $course instanceof Course ? $course->id : $course;

        return $query->where('course_id', $courseId);
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
            'waitlist_joined_at' => null,
            'waitlist_promoted_at' => null,
            'seat_hold_expires_at' => null,
        ])->save();

        if ($shouldIncreaseCount) {
            $this->adjustCourseStudentsCount(1);
        }

        try {
            app(\App\Services\PortalNotificationService::class)->notifyEnrollmentApproved($this->fresh());
        } catch (\Throwable $exception) {
            report($exception);
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
            'waitlist_promoted_at' => null,
            'seat_hold_expires_at' => null,
        ];

        if ($notes !== null) {
            $payload['notes'] = $notes;
        }

        $this->forceFill($payload)->save();

        if ($shouldDecreaseCount) {
            $this->adjustCourseStudentsCount(-1);
        }

        try {
            app(\App\Services\PortalNotificationService::class)->notifyEnrollmentRejected($this->fresh(), $this->notes);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    public function cancel($notes = null): void
    {
        $shouldDecreaseCount = in_array($this->status, ['approved', 'completed'], true);

        $payload = [
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'waitlist_promoted_at' => null,
            'seat_hold_expires_at' => null,
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
            'waitlist_joined_at' => null,
            'waitlist_promoted_at' => null,
            'seat_hold_expires_at' => null,
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

    public function isWaitlisted(): bool
    {
        return $this->isPending()
            && ! is_null($this->waitlist_joined_at)
            && is_null($this->waitlist_promoted_at);
    }

    public function hasActiveSeatHold(): bool
    {
        return $this->isPending()
            && ! is_null($this->waitlist_promoted_at)
            && ! is_null($this->seat_hold_expires_at)
            && $this->seat_hold_expires_at->isFuture();
    }

    public function getWaitlistPositionAttribute(): ?int
    {
        if (! $this->isWaitlisted()) {
            return null;
        }

        $joinedAt = $this->waitlist_joined_at ?: $this->created_at;

        return static::query()
            ->where('class_id', $this->class_id)
            ->pending()
            ->waitlisted()
            ->where(function ($query) use ($joinedAt) {
                $query->where('waitlist_joined_at', '<', $joinedAt)
                    ->orWhere(function ($sameTimeQuery) use ($joinedAt) {
                        $sameTimeQuery->where('waitlist_joined_at', $joinedAt)
                            ->where('id', '<=', $this->id);
                    });
            })
            ->count();
    }

    public function getDeliveryModeAttribute(): string
    {
        $this->loadMissing('courseClass.course');

        return $this->courseClass?->course?->delivery_mode ?? 'online';
    }

    public function getStatusTextAttribute()
    {
        if ($this->hasActiveSeatHold()) {
            return 'Đang giữ chỗ';
        }

        if ($this->isWaitlisted()) {
            return 'Trong hàng chờ';
        }

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
        if ($this->hasActiveSeatHold()) {
            return 'primary';
        }

        if ($this->isWaitlisted()) {
            return 'dark';
        }

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
        $this->loadMissing('course');

        $course = $this->course;

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
