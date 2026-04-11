<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\DiscountCode;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;

class PromotionService
{
    public function getAutomaticSettings(): array
    {
        return [
            'early_bird_enabled' => (string) Setting::get('promotion_early_bird_enabled', '1') === '1',
            'early_bird_days_before_start' => max(1, (int) Setting::get('promotion_early_bird_days_before_start', '14')),
            'early_bird_discount_type' => Setting::get('promotion_early_bird_discount_type', DiscountCode::VALUE_PERCENT),
            'early_bird_discount_value' => (float) Setting::get('promotion_early_bird_discount_value', '10'),
            'combo_enabled' => (string) Setting::get('promotion_combo_enabled', '1') === '1',
            'combo_required_courses' => max(1, (int) Setting::get('promotion_combo_required_courses', '1')),
            'combo_discount_type' => Setting::get('promotion_combo_discount_type', DiscountCode::VALUE_PERCENT),
            'combo_discount_value' => (float) Setting::get('promotion_combo_discount_value', '8'),
        ];
    }

    public function preview(?User $user, Course $course, ?CourseClass $class = null, ?string $voucherCode = null): array
    {
        $basePrice = $this->resolveBasePrice($course, $class);
        $automaticOptions = $this->buildAutomaticOptions($user, $course, $class, $basePrice);
        $bestAutomatic = collect($automaticOptions)
            ->sortByDesc('discount_amount')
            ->first();

        $voucher = null;
        $voucherOption = null;
        $voucherError = null;

        if (filled($voucherCode)) {
            [$voucher, $voucherOption, $voucherError] = $this->resolveVoucher($user, $course, $class, trim((string) $voucherCode), $basePrice);
        }

        $appliedItems = [];
        $discountAmount = 0.0;

        if ($bestAutomatic && $voucherOption) {
            if ($voucherOption['can_stack_with_auto'] ?? false) {
                $appliedItems[] = $bestAutomatic;
                $appliedItems[] = $voucherOption;
                $discountAmount = $bestAutomatic['discount_amount'] + $voucherOption['discount_amount'];
            } else {
                $winner = $voucherOption['discount_amount'] > $bestAutomatic['discount_amount']
                    ? $voucherOption
                    : $bestAutomatic;

                $appliedItems[] = $winner;
                $discountAmount = $winner['discount_amount'];
            }
        } elseif ($voucherOption) {
            $appliedItems[] = $voucherOption;
            $discountAmount = $voucherOption['discount_amount'];
        } elseif ($bestAutomatic) {
            $appliedItems[] = $bestAutomatic;
            $discountAmount = $bestAutomatic['discount_amount'];
        }

        $discountAmount = round(min($basePrice, max(0.0, $discountAmount)), 2);
        $payableAmount = round(max(0.0, $basePrice - $discountAmount), 2);

        return [
            'base_price' => $basePrice,
            'automatic_options' => $automaticOptions,
            'best_automatic' => $bestAutomatic,
            'voucher' => $voucher,
            'voucher_option' => $voucherOption,
            'voucher_error' => $voucherError,
            'applied_items' => $appliedItems,
            'discount_amount' => $discountAmount,
            'payable_amount' => $payableAmount,
            'savings_percentage' => $basePrice > 0 ? round(($discountAmount / $basePrice) * 100) : 0,
        ];
    }

    public function publicVoucherHints(?User $user, Course $course, ?CourseClass $class = null): Collection
    {
        return DiscountCode::query()
            ->active()
            ->public()
            ->orderByDesc('id')
            ->get()
            ->filter(function (DiscountCode $discountCode) use ($user, $course) {
                if (! $discountCode->isWithinWindow()) {
                    return false;
                }

                if (! $discountCode->hasRemainingQuota()) {
                    return false;
                }

                if (! $discountCode->appliesToCourse($course)) {
                    return false;
                }

                if ($user && ! $discountCode->isEligibleForUser($user)) {
                    return false;
                }

                return true;
            })
            ->values();
    }

    public function buildSnapshot(array $pricing): array
    {
        return [
            'base_price' => $pricing['base_price'],
            'discount_amount' => $pricing['discount_amount'],
            'payable_amount' => $pricing['payable_amount'],
            'applied_items' => collect($pricing['applied_items'] ?? [])->map(function (array $item) {
                return [
                    'slug' => $item['slug'] ?? null,
                    'title' => $item['title'] ?? null,
                    'description' => $item['description'] ?? null,
                    'discount_amount' => $item['discount_amount'] ?? 0,
                    'value_label' => $item['value_label'] ?? null,
                    'discount_code_id' => $item['discount_code_id'] ?? null,
                    'code' => $item['code'] ?? null,
                ];
            })->values()->all(),
        ];
    }

    public function resolveBasePrice(Course $course, ?CourseClass $class = null): float
    {
        if ($class && ! is_null($class->price_override)) {
            return round((float) $class->price_override, 2);
        }

        return round(! is_null($course->sale_price) ? (float) $course->sale_price : (float) $course->price, 2);
    }

    protected function buildAutomaticOptions(?User $user, Course $course, ?CourseClass $class, float $basePrice): array
    {
        $options = [];

        if ($earlyBird = $this->buildEarlyBirdOption($course, $class, $basePrice)) {
            $options[] = $earlyBird;
        }

        if ($combo = $this->buildComboOption($user, $course, $basePrice)) {
            $options[] = $combo;
        }

        return $options;
    }

    protected function buildEarlyBirdOption(Course $course, ?CourseClass $class, float $basePrice): ?array
    {
        if (! $class || ! $class->start_date || $basePrice <= 0) {
            return null;
        }

        $settings = $this->getAutomaticSettings();

        if (! $settings['early_bird_enabled']) {
            return null;
        }

        $daysUntilStart = now()->startOfDay()->diffInDays($class->start_date->copy()->startOfDay(), false);

        if ($daysUntilStart < $settings['early_bird_days_before_start']) {
            return null;
        }

        $discountAmount = $this->computeDiscount($basePrice, $settings['early_bird_discount_type'], $settings['early_bird_discount_value']);

        if ($discountAmount <= 0) {
            return null;
        }

        $valueLabel = $settings['early_bird_discount_type'] === DiscountCode::VALUE_FIXED
            ? number_format($settings['early_bird_discount_value'], 0) . 'đ'
            : rtrim(rtrim(number_format($settings['early_bird_discount_value'], 2), '0'), '.') . '%';

        return [
            'source' => 'automatic',
            'slug' => 'early_bird',
            'title' => 'Ưu đãi đăng ký sớm',
            'description' => 'Giảm khi đăng ký trước ngày khai giảng đủ sớm.',
            'value_label' => $valueLabel,
            'discount_amount' => $discountAmount,
            'discount_code_id' => null,
            'code' => null,
            'meta' => [
                'days_until_start' => $daysUntilStart,
                'days_rule' => $settings['early_bird_days_before_start'],
            ],
        ];
    }

    protected function buildComboOption(?User $user, Course $course, float $basePrice): ?array
    {
        if (! $user || $basePrice <= 0) {
            return null;
        }

        $settings = $this->getAutomaticSettings();

        if (! $settings['combo_enabled']) {
            return null;
        }

        $eligibleCount = $this->countEligibleComboCourses($user, $course);

        if ($eligibleCount < $settings['combo_required_courses']) {
            return null;
        }

        $discountAmount = $this->computeDiscount($basePrice, $settings['combo_discount_type'], $settings['combo_discount_value']);

        if ($discountAmount <= 0) {
            return null;
        }

        $valueLabel = $settings['combo_discount_type'] === DiscountCode::VALUE_FIXED
            ? number_format($settings['combo_discount_value'], 0) . 'đ'
            : rtrim(rtrim(number_format($settings['combo_discount_value'], 2), '0'), '.') . '%';

        $scopeLabel = filled($course->series_key) ? 'cùng lộ trình' : 'cùng nhóm ngành';

        return [
            'source' => 'automatic',
            'slug' => 'combo',
            'title' => 'Ưu đãi combo nhiều khóa',
            'description' => 'Giảm vì bạn đã có khóa học ' . $scopeLabel . '.',
            'value_label' => $valueLabel,
            'discount_amount' => $discountAmount,
            'discount_code_id' => null,
            'code' => null,
            'meta' => [
                'eligible_count' => $eligibleCount,
                'required_count' => $settings['combo_required_courses'],
            ],
        ];
    }

    protected function resolveVoucher(?User $user, Course $course, ?CourseClass $class, string $voucherCode, float $basePrice): array
    {
        if ($voucherCode === '') {
            return [null, null, null];
        }

        if (! $user) {
            return [null, null, 'Vui lòng đăng nhập để sử dụng mã giảm giá.'];
        }

        $discountCode = DiscountCode::query()
            ->where('code', strtoupper($voucherCode))
            ->first();

        if (! $discountCode || ! $discountCode->is_active) {
            return [null, null, 'Mã giảm giá không tồn tại hoặc đã bị tắt.'];
        }

        if (! $discountCode->isWithinWindow()) {
            return [$discountCode, null, 'Mã giảm giá hiện chưa nằm trong thời gian áp dụng.'];
        }

        if (! $discountCode->hasRemainingQuota()) {
            return [$discountCode, null, 'Mã giảm giá đã hết lượt sử dụng.'];
        }

        if (! $discountCode->hasRemainingQuotaForUser($user)) {
            return [$discountCode, null, 'Bạn đã sử dụng hết số lần cho phép của mã này.'];
        }

        if (! $discountCode->appliesToCourse($course)) {
            return [$discountCode, null, 'Mã giảm giá này không áp dụng cho khóa học bạn chọn.'];
        }

        if (! $discountCode->isEligibleForUser($user)) {
            return [$discountCode, null, 'Mã giảm giá này chỉ áp dụng cho học viên mới.'];
        }

        if (! is_null($discountCode->min_order_amount) && $basePrice < (float) $discountCode->min_order_amount) {
            return [$discountCode, null, 'Đơn đăng ký chưa đạt mức tối thiểu để dùng mã giảm giá này.'];
        }

        $discountAmount = $discountCode->computeDiscount($basePrice);

        if ($discountAmount <= 0) {
            return [$discountCode, null, 'Mã giảm giá hiện không áp dụng được cho giao dịch này.'];
        }

        return [
            $discountCode,
            [
                'source' => 'voucher',
                'slug' => 'voucher',
                'title' => $discountCode->title,
                'description' => $discountCode->description ?: 'Áp dụng qua mã giảm giá ' . $discountCode->code . '.',
                'value_label' => $discountCode->value_label,
                'discount_amount' => $discountAmount,
                'discount_code_id' => $discountCode->id,
                'code' => $discountCode->code,
                'can_stack_with_auto' => $discountCode->can_stack_with_auto,
            ],
            null,
        ];
    }

    protected function countEligibleComboCourses(User $user, Course $course): int
    {
        $query = CourseEnrollment::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['approved', 'completed'])
            ->whereHas('courseClass', function ($classQuery) use ($course) {
                $classQuery->where('course_id', '!=', $course->id)
                    ->whereHas('course', function ($courseQuery) use ($course) {
                        if (filled($course->series_key)) {
                            $courseQuery->where('series_key', $course->series_key);
                        } else {
                            $courseQuery->where('category_id', $course->category_id);
                        }
                    });
            });

        return $query->count();
    }

    protected function computeDiscount(float $basePrice, string $type, float $value): float
    {
        if ($basePrice <= 0 || $value <= 0) {
            return 0.0;
        }

        $discount = $type === DiscountCode::VALUE_FIXED
            ? $value
            : ($basePrice * $value) / 100;

        return round(min($basePrice, max(0.0, $discount)), 2);
    }
}
