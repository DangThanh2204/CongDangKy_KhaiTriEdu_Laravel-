<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DiscountCode extends Model
{
    use HasFactory;

    public const AUDIENCE_ALL = 'all';
    public const AUDIENCE_NEW_STUDENT = 'new_student';

    public const SCOPE_ALL = 'all';
    public const SCOPE_COURSE = 'course';
    public const SCOPE_CATEGORY = 'category';
    public const SCOPE_SERIES = 'series';

    public const VALUE_PERCENT = 'percent';
    public const VALUE_FIXED = 'fixed';

    protected $fillable = [
        'code',
        'title',
        'description',
        'audience',
        'scope_type',
        'course_id',
        'category_id',
        'series_key',
        'value_type',
        'value',
        'min_order_amount',
        'usage_limit',
        'per_user_limit',
        'can_stack_with_auto',
        'is_public',
        'is_active',
        'starts_at',
        'ends_at',
        'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'can_stack_with_auto' => 'boolean',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $discountCode) {
            $discountCode->code = strtoupper(trim((string) $discountCode->code));

            if ($discountCode->scope_type !== self::SCOPE_COURSE) {
                $discountCode->course_id = null;
            }

            if ($discountCode->scope_type !== self::SCOPE_CATEGORY) {
                $discountCode->category_id = null;
            }

            if ($discountCode->scope_type !== self::SCOPE_SERIES) {
                $discountCode->series_key = null;
            }
        });
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function category()
    {
        return $this->belongsTo(CourseCategory::class, 'category_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class, 'discount_code_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'discount_code_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function isWithinWindow(): bool
    {
        $now = now();

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return true;
    }

    public function appliesToCourse(Course $course): bool
    {
        return match ($this->scope_type) {
            self::SCOPE_COURSE => (int) $this->course_id === (int) $course->id,
            self::SCOPE_CATEGORY => (int) $this->category_id === (int) $course->category_id,
            self::SCOPE_SERIES => filled($this->series_key) && $this->series_key === $course->series_key,
            default => true,
        };
    }

    public function isEligibleForUser(?User $user): bool
    {
        if (! $user) {
            return $this->audience === self::AUDIENCE_ALL;
        }

        if ($this->audience === self::AUDIENCE_NEW_STUDENT) {
            return ! CourseEnrollment::query()
                ->where('user_id', $user->id)
                ->whereIn('status', ['approved', 'completed'])
                ->exists();
        }

        return true;
    }

    public function hasRemainingQuota(): bool
    {
        if ($this->usage_limit === null) {
            return true;
        }

        return $this->usage_count < $this->usage_limit;
    }

    public function hasRemainingQuotaForUser(User $user): bool
    {
        if ($this->per_user_limit === null) {
            return true;
        }

        return $this->usageCountForUser($user) < $this->per_user_limit;
    }

    public function usageCountForUser(User $user): int
    {
        return $this->enrollments()
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->count();
    }

    public function computeDiscount(float $baseAmount): float
    {
        if ($baseAmount <= 0) {
            return 0.0;
        }

        $discount = $this->value_type === self::VALUE_FIXED
            ? (float) $this->value
            : ($baseAmount * (float) $this->value) / 100;

        return round(min($baseAmount, max(0.0, $discount)), 2);
    }

    public function getUsageCountAttribute(): int
    {
        return $this->enrollments()
            ->whereNotIn('status', ['rejected', 'cancelled'])
            ->count();
    }

    public function getAudienceLabelAttribute(): string
    {
        return match ($this->audience) {
            self::AUDIENCE_NEW_STUDENT => 'Học viên mới',
            default => 'Tất cả học viên',
        };
    }

    public function getScopeLabelAttribute(): string
    {
        return match ($this->scope_type) {
            self::SCOPE_COURSE => 'Theo khóa học',
            self::SCOPE_CATEGORY => 'Theo nhóm ngành',
            self::SCOPE_SERIES => 'Theo lộ trình / series',
            default => 'Toàn hệ thống',
        };
    }

    public function getValueLabelAttribute(): string
    {
        if ($this->value_type === self::VALUE_FIXED) {
            return number_format((float) $this->value, 0) . 'đ';
        }

        return rtrim(rtrim(number_format((float) $this->value, 2), '0'), '.') . '%';
    }

    public function getStatusLabelAttribute(): string
    {
        if (! $this->is_active) {
            return 'Đang tắt';
        }

        if (! $this->isWithinWindow()) {
            return 'Ngoài thời gian áp dụng';
        }

        if (! $this->hasRemainingQuota()) {
            return 'Hết lượt dùng';
        }

        return 'Đang hoạt động';
    }

    public function getSummaryLabelAttribute(): string
    {
        $parts = collect([
            $this->value_label,
            $this->audience_label,
            $this->scope_label,
        ])->filter();

        return $parts->implode(' • ');
    }
}