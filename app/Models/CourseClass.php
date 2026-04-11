<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\MongoModel as Model;

class CourseClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'name',
        'course_id',
        'instructor_id',
        'start_date',
        'end_date',
        'schedule',
        'meeting_info',
        'max_students',
        'price_override',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'max_students' => 'integer',
        'price_override' => 'decimal:2',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor()
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class, 'class_id');
    }

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class, 'class_id');
    }

    public function getCurrentStudentsCountAttribute()
    {
        return $this->enrollments()->whereIn('status', ['approved', 'completed'])->count();
    }

    public function getHeldSeatsCountAttribute(): int
    {
        if (($this->max_students ?: 0) <= 0) {
            return 0;
        }

        return $this->enrollments()
            ->holdingSeat()
            ->count();
    }

    public function getWaitlistCountAttribute(): int
    {
        return $this->enrollments()
            ->pending()
            ->waitlisted()
            ->count();
    }

    public function getRemainingSlotsAttribute(): ?int
    {
        if (($this->max_students ?: 0) <= 0) {
            return null;
        }

        return max(0, $this->max_students - $this->current_students_count - $this->held_seats_count);
    }

    public function getIsFullAttribute()
    {
        if (($this->max_students ?: 0) <= 0) {
            return false;
        }

        return $this->remaining_slots <= 0;
    }

    public function getDeliveryModeAttribute(): string
    {
        return $this->course?->delivery_mode ?? 'offline';
    }

    public function getDeliveryModeLabelAttribute(): string
    {
        return $this->delivery_mode === 'online' ? 'Online' : 'Offline';
    }

    public function getListingPriceAttribute(): float
    {
        if ($this->price_override !== null) {
            return (float) $this->price_override;
        }

        return (float) ($this->course?->final_price ?? 0);
    }

    public function isOnline(): bool
    {
        return $this->delivery_mode === 'online';
    }

    public function isOffline(): bool
    {
        return $this->delivery_mode === 'offline';
    }

    public function getStatusBadgeAttribute()
    {
        return $this->status === 'active' ? 'success' : 'secondary';
    }

    public function getStatusTextAttribute()
    {
        return $this->status === 'active' ? 'Má»Ÿ Ä‘Äƒng kÃ½' : 'Táº¡m dá»«ng';
    }

    public function getStructuredScheduleLinesAttribute()
    {
        $schedules = $this->relationLoaded('schedules') ? $this->schedules : $this->schedules()->get();

        if ($schedules->isEmpty()) {
            return [];
        }

        $weekdayOrder = ['2' => 2, '3' => 3, '4' => 4, '5' => 5, '6' => 6, '7' => 7, 'CN' => 8];
        $weekdayLabels = ['2' => 'Thá»© 2', '3' => 'Thá»© 3', '4' => 'Thá»© 4', '5' => 'Thá»© 5', '6' => 'Thá»© 6', '7' => 'Thá»© 7', 'CN' => 'Chá»§ nháº­t'];

        return $schedules
            ->sortBy(function ($item) use ($weekdayOrder) {
                return $weekdayOrder[$item->weekday] ?? 99;
            })
            ->groupBy('weekday')
            ->map(function ($daySchedules, $weekday) use ($weekdayLabels) {
                $timeRanges = $daySchedules
                    ->sortBy('start_time')
                    ->map(function ($schedule) {
                        if ($schedule->start_time && $schedule->end_time) {
                            return date('H:i', strtotime($schedule->start_time)) . ' - ' . date('H:i', strtotime($schedule->end_time));
                        }

                        if ($schedule->start_time) {
                            return 'Báº¯t Ä‘áº§u ' . date('H:i', strtotime($schedule->start_time));
                        }

                        if ($schedule->end_time) {
                            return 'Káº¿t thÃºc ' . date('H:i', strtotime($schedule->end_time));
                        }

                        return null;
                    })
                    ->filter()
                    ->values();

                $label = $weekdayLabels[$weekday] ?? $weekday;

                if ($timeRanges->isEmpty()) {
                    return $label;
                }

                return $label . ': ' . $timeRanges->implode(', ');
            })
            ->values()
            ->all();
    }

    public function getScheduleTextAttribute()
    {
        $lines = $this->structured_schedule_lines;

        if (! empty($lines)) {
            return implode(' | ', $lines);
        }

        return $this->schedule;
    }
}
