<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\DiscountCode;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPromotionController extends Controller
{
    public function index(): View
    {
        $settings = [
            'promotion_early_bird_enabled' => Setting::get('promotion_early_bird_enabled', '1'),
            'promotion_early_bird_days_before_start' => Setting::get('promotion_early_bird_days_before_start', '14'),
            'promotion_early_bird_discount_type' => Setting::get('promotion_early_bird_discount_type', DiscountCode::VALUE_PERCENT),
            'promotion_early_bird_discount_value' => Setting::get('promotion_early_bird_discount_value', '10'),
            'promotion_combo_enabled' => Setting::get('promotion_combo_enabled', '1'),
            'promotion_combo_required_courses' => Setting::get('promotion_combo_required_courses', '1'),
            'promotion_combo_discount_type' => Setting::get('promotion_combo_discount_type', DiscountCode::VALUE_PERCENT),
            'promotion_combo_discount_value' => Setting::get('promotion_combo_discount_value', '8'),
        ];

        $discountSchemaReady = $this->hasDiscountSchema();

        $discountCodes = $discountSchemaReady
            ? DiscountCode::query()
                ->with(['course:id,title', 'category:id,name'])
                ->orderByDesc('id')
                ->get()
            : collect();

        $courses = Course::query()
            ->select('id', 'title', 'series_key')
            ->orderBy('title')
            ->get();

        $categories = CourseCategory::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('admin.promotions.index', compact('settings', 'discountCodes', 'courses', 'categories', 'discountSchemaReady'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'promotion_early_bird_enabled' => 'nullable|in:0,1',
            'promotion_early_bird_days_before_start' => 'nullable|integer|min:1|max:365',
            'promotion_early_bird_discount_type' => 'required|in:percent,fixed',
            'promotion_early_bird_discount_value' => 'required|numeric|min:0',
            'promotion_combo_enabled' => 'nullable|in:0,1',
            'promotion_combo_required_courses' => 'nullable|integer|min:1|max:20',
            'promotion_combo_discount_type' => 'required|in:percent,fixed',
            'promotion_combo_discount_value' => 'required|numeric|min:0',
        ]);

        $payload = [
            'promotion_early_bird_enabled' => $validated['promotion_early_bird_enabled'] ?? '0',
            'promotion_early_bird_days_before_start' => (string) ($validated['promotion_early_bird_days_before_start'] ?? 14),
            'promotion_early_bird_discount_type' => $validated['promotion_early_bird_discount_type'],
            'promotion_early_bird_discount_value' => (string) $validated['promotion_early_bird_discount_value'],
            'promotion_combo_enabled' => $validated['promotion_combo_enabled'] ?? '0',
            'promotion_combo_required_courses' => (string) ($validated['promotion_combo_required_courses'] ?? 1),
            'promotion_combo_discount_type' => $validated['promotion_combo_discount_type'],
            'promotion_combo_discount_value' => (string) $validated['promotion_combo_discount_value'],
        ];

        foreach ($payload as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã cập nhật cấu hình ưu đãi tự động.');
    }

    public function storeCode(Request $request): RedirectResponse
    {
        if (! $this->hasDiscountSchema()) {
            return $this->redirectVoucherSchemaUnavailable();
        }

        $validated = $request->validate([
            'title' => 'required|string|max:160',
            'code' => 'required|string|max:50|unique:discount_codes,code',
            'description' => 'nullable|string|max:2000',
            'audience' => 'required|in:all,new_student',
            'scope_type' => 'required|in:all,course,category,series',
            'course_id' => 'nullable|required_if:scope_type,course|exists:courses,id',
            'category_id' => 'nullable|required_if:scope_type,category|exists:course_categories,id',
            'series_key' => 'nullable|required_if:scope_type,series|string|max:255',
            'value_type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:0.01',
            'min_order_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'per_user_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'can_stack_with_auto' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        DiscountCode::create([
            'title' => $validated['title'],
            'code' => strtoupper(trim($validated['code'])),
            'description' => $validated['description'] ?? null,
            'audience' => $validated['audience'],
            'scope_type' => $validated['scope_type'],
            'course_id' => $validated['scope_type'] === DiscountCode::SCOPE_COURSE ? $validated['course_id'] : null,
            'category_id' => $validated['scope_type'] === DiscountCode::SCOPE_CATEGORY ? $validated['category_id'] : null,
            'series_key' => $validated['scope_type'] === DiscountCode::SCOPE_SERIES ? trim((string) ($validated['series_key'] ?? '')) : null,
            'value_type' => $validated['value_type'],
            'value' => $validated['value'],
            'min_order_amount' => $validated['min_order_amount'] ?? null,
            'usage_limit' => $validated['usage_limit'] ?? null,
            'per_user_limit' => $validated['per_user_limit'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'can_stack_with_auto' => $request->boolean('can_stack_with_auto'),
            'is_public' => $request->boolean('is_public'),
            'is_active' => $request->has('is_active'),
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã tạo mã giảm giá mới.');
    }

    public function toggleCode(string $discountCode): RedirectResponse
    {
        if (! $this->hasDiscountSchema()) {
            return $this->redirectVoucherSchemaUnavailable();
        }

        $discountCode = DiscountCode::query()->findOrFail($discountCode);

        $discountCode->update([
            'is_active' => ! $discountCode->is_active,
        ]);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã cập nhật trạng thái mã giảm giá.');
    }

    public function destroyCode(string $discountCode): RedirectResponse
    {
        if (! $this->hasDiscountSchema()) {
            return $this->redirectVoucherSchemaUnavailable();
        }

        $discountCode = DiscountCode::query()->findOrFail($discountCode);
        $discountCode->delete();

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã xóa mã giảm giá.');
    }

    protected function hasDiscountSchema(): bool
    {
        return config('database.default') === 'mongodb' || DiscountCode::query()->count() >= 0;
    }

    protected function redirectVoucherSchemaUnavailable(): RedirectResponse
    {
        return redirect()
            ->route('admin.promotions.index')
            ->with('error', 'Hệ thống voucher chưa sẵn sàng trên môi trường này. Vui lòng chạy cập nhật dữ liệu trước.');
    }
}
