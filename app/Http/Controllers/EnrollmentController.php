<?php

namespace App\Http\Controllers;

use App\Models\ClassChangeLog;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\Setting;
use App\Notifications\RefundIssuedNotification;
use App\Services\BlockchainAuditService;
use App\Services\FireflyService;
use App\Services\VnpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class EnrollmentController extends Controller
{
    public function __construct(
        protected FireflyService $firefly,
        protected BlockchainAuditService $blockchainAudit,
        protected VnpayService $vnpay,
    ) {
    }

    public function enroll(Request $request, Course $course)
    {
        if ($course->status !== 'published') {
            return back()->with('error', 'KhÃ³a há»c khÃ´ng kháº£ dá»¥ng.');
        }

        $class = $this->resolveSelectedClass($request, $course);

        if (! $class) {
            $message = $course->isOffline()
                ? 'Vui lÃ²ng chá»n má»™t Ä‘á»£t há»c há»£p lá»‡ Ä‘ang má»Ÿ Ä‘Äƒng kÃ½.'
                : 'KhÃ³a há»c nÃ y hiá»‡n chÆ°a má»Ÿ Ä‘Äƒng kÃ½.';

            return back()->withInput()->with('error', $message);
        }

        if ($class->status !== 'active') {
            return back()->withInput()->with('error', $course->isOffline()
                ? 'Äá»£t há»c nÃ y hiá»‡n chÆ°a má»Ÿ Ä‘Äƒng kÃ½.'
                : 'KhÃ³a há»c nÃ y hiá»‡n chÆ°a má»Ÿ Ä‘Äƒng kÃ½.');
        }

        if ($course->isOffline() && $class->is_full) {
            return back()->withInput()->with('error', 'Äá»£t há»c nÃ y Ä‘Ã£ Ä‘á»§ sá»‘ lÆ°á»£ng há»c viÃªn.');
        }

        $existing = CourseEnrollment::where('user_id', Auth::id())
            ->forCourse($course)
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->latest('id')
            ->first();

        if ($existing) {
            $currentClassName = $existing->courseClass->name ?? null;

            if ($existing->isPending()) {
                return back()->with('error', $currentClassName
                    ? 'Báº¡n Ä‘Ã£ cÃ³ yÃªu cáº§u Ä‘Äƒng kÃ½ chá» duyá»‡t á»Ÿ Ä‘á»£t há»c ' . $currentClassName . '.'
                    : 'Báº¡n Ä‘Ã£ cÃ³ yÃªu cáº§u Ä‘Äƒng kÃ½ khÃ³a há»c nÃ y Ä‘ang chá» duyá»‡t.');
            }

            if ($existing->isApproved() || $existing->isCompleted()) {
                return back()->with('error', $currentClassName
                    ? 'Báº¡n Ä‘Ã£ Ä‘Äƒng kÃ½ khÃ³a há»c nÃ y á»Ÿ Ä‘á»£t há»c ' . $currentClassName . ' rá»“i.'
                    : 'Báº¡n Ä‘Ã£ Ä‘Äƒng kÃ½ khÃ³a há»c nÃ y rá»“i.');
            }
        }

        if ($course->isOnline() && $course->series_key) {
            $existingSeries = CourseEnrollment::where('user_id', Auth::id())
                ->whereHas('courseClass', function ($query) use ($course) {
                    $query->whereHas('course', function ($courseQuery) use ($course) {
                        $courseQuery->where('series_key', $course->series_key);
                    });
                })
                ->whereIn('status', ['approved', 'completed'])
                ->exists();

            if ($existingSeries) {
                $enrollment = $this->storeEnrollmentRequest(Auth::id(), $class);
                $enrollment->approve();

                return back()->with('success', 'ÄÄƒng kÃ½ thÃ nh cÃ´ng, báº¡n cÃ³ thá»ƒ há»c ngay.');
            }
        }

        $finalPrice = (float) $course->final_price;
        $requiresManualApproval = $course->requiresManualApproval();

        if ($finalPrice > 0) {
            $method = $request->input('payment_method', 'wallet');

            if ($method === 'wallet') {
                $wallet = Auth::user()->getOrCreateWallet();

                if ($wallet->balance < $finalPrice) {
                    return back()
                        ->with('error', 'Sá»‘ dÆ° khÃ´ng Ä‘á»§. Vui lÃ²ng náº¡p tiá»n vÃ o vÃ­ Ä‘á»ƒ mua khÃ³a há»c.')
                        ->with('required_topup', true);
                }

                $transaction = $wallet->charge($finalPrice, [
                    'course_id' => $course->id,
                    'class_id' => $class->id,
                ]);

                $platformIdentity = $this->firefly->getPlatformIdentity();
                $purchaseReference = $transaction?->reference ?: ('COURSE-' . $course->id . '-' . Auth::id() . '-' . now()->format('YmdHis'));
                $fireflyResult = $this->firefly->transfer(
                    $wallet->firefly_identity,
                    $platformIdentity,
                    $finalPrice,
                    [
                        'reference' => $purchaseReference,
                        'data' => [
                            'type' => 'course_purchase',
                            'wallet_transaction_id' => $transaction?->id,
                            'course_id' => $course->id,
                            'class_id' => $class->id,
                            'user_id' => Auth::id(),
                            'amount' => (float) $finalPrice,
                        ],
                    ]
                );

                $auditResponse = $this->blockchainAudit->record('wallet.course_purchase', [
                    'wallet_transaction_id' => $transaction?->id,
                    'course_id' => $course->id,
                    'course_title' => $course->title,
                    'class_id' => $class->id,
                    'class_name' => $class->name,
                    'user_id' => Auth::id(),
                    'amount' => (float) $finalPrice,
                    'wallet_firefly_identity' => $wallet->firefly_identity,
                    'platform_identity' => $platformIdentity,
                    'firefly_tx_id' => $fireflyResult['tx_id'] ?? null,
                    'firefly_message_id' => $fireflyResult['message_id'] ?? null,
                ], [
                    'reference' => $purchaseReference,
                    'user_id' => Auth::id(),
                    'username' => Auth::user()?->username,
                    'role' => Auth::user()?->role,
                    'ip' => $request->ip(),
                ]);

                if ($transaction) {
                    $transaction->update([
                        'metadata' => array_merge($transaction->metadata ?? [], [
                            'firefly' => $fireflyResult,
                            'blockchain_audit' => $auditResponse,
                        ]),
                    ]);
                }

                $enrollment = $this->storeEnrollmentRequest(Auth::id(), $class);

                if ($requiresManualApproval) {
                    return back()->with('success', 'ÄÄƒng kÃ½ thÃ nh cÃ´ng, vui lÃ²ng chá» admin duyá»‡t.');
                }

                $enrollment->approve();

                return back()->with('success', 'ÄÄƒng kÃ½ thÃ nh cÃ´ng, báº¡n cÃ³ thá»ƒ há»c ngay.');
            }

            if ($method === 'vnpay') {
                if (! $this->vnpay->isConfigured()) {
                    return back()
                        ->withInput()
                        ->with('error', 'VNPay chua duoc cau hinh day du: ' . implode(' ', $this->vnpay->configurationIssues()));
                }

                $payment = \App\Models\Payment::query()
                    ->where('user_id', Auth::id())
                    ->where('class_id', $class->id)
                    ->where('method', 'vnpay')
                    ->where('status', 'pending')
                    ->latest('id')
                    ->first();

                if (! $payment) {
                    $reference = 'VNP' . now()->format('YmdHis') . Auth::id() . $class->id . random_int(100, 999);

                    $payment = \App\Models\Payment::create([
                        'user_id' => Auth::id(),
                        'class_id' => $class->id,
                        'amount' => $finalPrice,
                        'method' => 'vnpay',
                        'status' => 'pending',
                        'reference' => $reference,
                        'notes' => 'Khá»Ÿi táº¡o thanh toÃ¡n VNPay',
                    ]);
                }

                return redirect()
                    ->route('payments.vnpay.redirect', $payment)
                    ->with('success', 'Äang chuyá»ƒn sang cá»•ng thanh toÃ¡n VNPay.');
            }

            if (in_array($method, ['bank_transfer', 'cash', 'counter'], true)) {
                $reference = 'PAY' . strtoupper(uniqid());

                $payment = \App\Models\Payment::create([
                    'user_id' => Auth::id(),
                    'class_id' => $class->id,
                    'amount' => $finalPrice,
                    'method' => $method,
                    'status' => 'pending',
                    'reference' => $reference,
                ]);

                $this->storeEnrollmentRequest(Auth::id(), $class);

                return redirect()
                    ->route('payments.show', $payment->id)
                    ->with('success', $requiresManualApproval
                        ? 'ÄÄƒng kÃ½ thÃ nh cÃ´ng, vui lÃ²ng thanh toÃ¡n vÃ  chá» admin duyá»‡t.'
                        : 'Phiáº¿u thanh toÃ¡n Ä‘Ã£ Ä‘Æ°á»£c táº¡o. Sau khi thanh toÃ¡n thÃ nh cÃ´ng, báº¡n sáº½ Ä‘Æ°á»£c xá»­ lÃ½ Ä‘Äƒng kÃ½.');
            }
        }

        $enrollment = $this->storeEnrollmentRequest(Auth::id(), $class);

        if ($requiresManualApproval) {
            return back()->with('success', 'ÄÄƒng kÃ½ thÃ nh cÃ´ng, vui lÃ²ng chá» admin duyá»‡t.');
        }

        $enrollment->approve();

        return back()->with('success', 'ÄÄƒng kÃ½ thÃ nh cÃ´ng, báº¡n cÃ³ thá»ƒ há»c ngay.');
    }

    public function unenroll(Course $course)
    {
        $enrollment = CourseEnrollment::where('user_id', Auth::id())
            ->forCourse($course)
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->latest('id')
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'Báº¡n chÆ°a Ä‘Äƒng kÃ½ khÃ³a há»c nÃ y.');
        }

        if ($enrollment->isCompleted()) {
            return back()->with('error', 'KhÃ´ng thá»ƒ há»§y Ä‘Äƒng kÃ½ Ä‘Ã£ hoÃ n thÃ nh hoáº·c bá»‹ tá»« chá»‘i.');
        }

        $wasPending = $enrollment->isPending();
        $class = $enrollment->class;
        $now = now();

        if ($class && $class->start_date && $now->lt($class->start_date)) {
            try {
                $wallet = Auth::user()->getOrCreateWallet();
                $purchase = $wallet->transactions()
                    ->where('type', 'purchase')
                    ->where('status', 'completed')
                    ->where('metadata->course_id', $course->id)
                    ->where('metadata->class_id', $class->id)
                    ->latest()
                    ->first();

                if ($purchase) {
                    $refundTx = $wallet->deposit($purchase->amount, [
                        'refunded_purchase_id' => $purchase->id,
                        'course_id' => $course->id,
                        'class_id' => $class->id,
                        'reason' => 'refund_on_unenroll_before_start',
                    ]);

                    if ($refundTx) {
                        \App\Services\SystemLogService::record('transaction', 'refund_issued', [
                            'purchase_id' => $purchase->id,
                            'refund_tx_id' => $refundTx->id,
                            'amount' => $refundTx->amount,
                        ], $refundTx->reference ?? null);

                        $refundReference = $refundTx->reference ?: ('REFUND-' . $refundTx->id);
                        $platformIdentity = $this->firefly->getPlatformIdentity();
                        $refundFireflyResult = $this->firefly->transfer(
                            $platformIdentity,
                            $wallet->firefly_identity,
                            (float) $refundTx->amount,
                            [
                                'reference' => $refundReference,
                                'data' => [
                                    'type' => 'course_refund',
                                    'refund_transaction_id' => $refundTx->id,
                                    'purchase_transaction_id' => $purchase->id,
                                    'course_id' => $course->id,
                                    'class_id' => $class->id,
                                    'user_id' => Auth::id(),
                                    'amount' => (float) $refundTx->amount,
                                ],
                            ]
                        );

                        $refundAudit = $this->blockchainAudit->record('wallet.refund_issued', [
                            'refund_transaction_id' => $refundTx->id,
                            'purchase_transaction_id' => $purchase->id,
                            'course_id' => $course->id,
                            'course_title' => $course->title,
                            'class_id' => $class->id,
                            'class_name' => $class->name,
                            'user_id' => Auth::id(),
                            'amount' => (float) $refundTx->amount,
                            'wallet_firefly_identity' => $wallet->firefly_identity,
                            'platform_identity' => $platformIdentity,
                            'firefly_tx_id' => $refundFireflyResult['tx_id'] ?? null,
                            'firefly_message_id' => $refundFireflyResult['message_id'] ?? null,
                        ], [
                            'reference' => $refundReference,
                            'user_id' => Auth::id(),
                            'username' => Auth::user()?->username,
                            'role' => Auth::user()?->role,
                            'ip' => request()->ip(),
                        ]);

                        $refundTx->update([
                            'metadata' => array_merge($refundTx->metadata ?? [], [
                                'firefly' => $refundFireflyResult,
                                'blockchain_audit' => $refundAudit,
                            ]),
                        ]);
                    }

                    try {
                        if ($refundTx) {
                            Auth::user()->notify(new RefundIssuedNotification($refundTx->amount, $course->title ?? null, $class->name ?? null));
                        }
                    } catch (\Exception $exception) {
                    }
                }
            } catch (\Exception $exception) {
            }
        }

        $enrollment->cancel('student_cancelled');

        return back()->with('success', $wasPending
            ? 'ÄÃ£ há»§y yÃªu cáº§u Ä‘Äƒng kÃ½.'
            : 'ÄÃ£ há»§y Ä‘Äƒng kÃ½ khÃ³a há»c.');
    }

    public function changeClass(Request $request, Course $course, CourseEnrollment $enrollment)
    {
        abort_unless($enrollment->user_id === Auth::id(), 403);

        $enrollment->loadMissing(['courseClass.course']);

        abort_unless((int) optional($enrollment->courseClass)->course_id === (int) $course->id, 404);

        if ($enrollment->isCompleted() || $enrollment->isRejected()) {
            return back()->with('error', 'KhÃ´ng thá»ƒ Ä‘á»•i Ä‘á»£t há»c cho Ä‘Äƒng kÃ½ nÃ y.');
        }

        if ((string) Setting::get('allow_class_change', '1') === '0') {
            return back()->with('error', 'Chá»©c nÄƒng Ä‘á»•i Ä‘á»£t há»c hiá»‡n Ä‘ang bá»‹ khÃ³a.');
        }

        if ($course->learning_type === 'online') {
            return back()->with('error', 'KhÃ³a há»c trá»±c tuyáº¿n khÃ´ng Ã¡p dá»¥ng Ä‘á»•i Ä‘á»£t há»c.');
        }

        $validated = $request->validate([
            'new_class_id' => 'required|exists:classes,id',
        ]);

        $currentClass = $enrollment->courseClass;
        $newClass = $course->classes()
            ->whereKey($validated['new_class_id'])
            ->first();

        if (! $newClass) {
            return back()->with('error', 'Äá»£t há»c má»›i khÃ´ng thuá»™c khÃ³a há»c nÃ y.');
        }

        if ($currentClass && (int) $currentClass->id === (int) $newClass->id) {
            return back()->with('error', 'Báº¡n Ä‘ang á»Ÿ Ä‘á»£t há»c nÃ y rá»“i.');
        }

        if ($newClass->status !== 'active') {
            return back()->with('error', 'Äá»£t há»c má»›i hiá»‡n chÆ°a má»Ÿ Ä‘Äƒng kÃ½.');
        }

        if ($newClass->is_full) {
            return back()->with('error', 'Äá»£t há»c má»›i Ä‘Ã£ Ä‘á»§ sá»‘ lÆ°á»£ng há»c viÃªn.');
        }

        $now = now();

        if ($currentClass?->start_date && $now->greaterThanOrEqualTo($currentClass->start_date->copy()->startOfDay())) {
            return back()->with('error', 'KhÃ´ng thá»ƒ Ä‘á»•i Ä‘á»£t há»c sau khi Ä‘á»£t hiá»‡n táº¡i Ä‘Ã£ báº¯t Ä‘áº§u.');
        }

        if ($newClass->start_date && $now->greaterThanOrEqualTo($newClass->start_date->copy()->startOfDay())) {
            return back()->with('error', 'KhÃ´ng thá»ƒ chuyá»ƒn sang Ä‘á»£t há»c Ä‘Ã£ báº¯t Ä‘áº§u.');
        }

        $deadlineDays = (int) Setting::get('class_change_deadline_days', '0');
        if ($deadlineDays > 0 && $newClass->start_date) {
            $deadline = $newClass->start_date->copy()->startOfDay()->subDays($deadlineDays);

            if ($now->greaterThan($deadline)) {
                return back()->with('error', 'ÄÃ£ quÃ¡ háº¡n Ä‘á»•i Ä‘á»£t há»c cho Ä‘á»£t báº¡n chá»n.');
            }
        }

        $oldClassId = $currentClass?->id;

        $enrollment->update([
            'class_id' => $newClass->id,
        ]);

        if (Schema::hasTable('class_change_logs')) {
            ClassChangeLog::create([
                'enrollment_id' => $enrollment->id,
                'user_id' => Auth::id(),
                'old_class_id' => $oldClassId,
                'new_class_id' => $newClass->id,
                'reason' => 'student_self_service',
            ]);
        }

        return back()->with('success', 'ÄÃ£ chuyá»ƒn sang Ä‘á»£t há»c ' . $newClass->name . '.');
    }

    private function resolveSelectedClass(Request $request, Course $course): ?CourseClass
    {
        if ($course->isOffline()) {
            if (! $request->filled('class_id')) {
                return null;
            }

            return $course->classes()
                ->whereKey($request->integer('class_id'))
                ->first();
        }

        if ($request->filled('class_id')) {
            return $course->classes()
                ->whereKey($request->integer('class_id'))
                ->first();
        }

        $activeClass = $course->classes()
            ->where('status', 'active')
            ->orderBy('start_date')
            ->orderBy('id')
            ->first();

        if ($activeClass) {
            return $activeClass;
        }

        if ($course->classes()->exists()) {
            return null;
        }

        return $course->resolveEnrollmentClass();
    }

    private function storeEnrollmentRequest(int $userId, CourseClass $class, array $attributes = []): CourseEnrollment
    {
        $enrollment = CourseEnrollment::firstOrNew([
            'user_id' => $userId,
            'class_id' => $class->id,
        ]);

        $enrollment->forceFill(array_merge([
            'status' => 'pending',
            'notes' => null,
            'enrolled_at' => null,
            'approved_at' => null,
            'rejected_at' => null,
            'cancelled_at' => null,
            'completed_at' => null,
        ], $attributes));

        $enrollment->save();

        return $enrollment;
    }

    private function extractScheduleDays(?string $schedule): array
    {
        if (! $schedule) {
            return [];
        }

        $normalized = mb_strtolower($schedule);
        $days = [];

        if (preg_match_all('/\b[2-7]\b/u', $normalized, $matches)) {
            foreach ($matches[0] as $day) {
                $days[] = (string) $day;
            }
        }

        if (preg_match('/chá»§\s*nháº­t|chu\s*nhat|\bcn\b/u', $normalized)) {
            $days[] = 'CN';
        }

        return array_values(array_unique($days));
    }
}
