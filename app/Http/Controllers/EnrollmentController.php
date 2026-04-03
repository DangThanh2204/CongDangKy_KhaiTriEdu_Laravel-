<?php

namespace App\Http\Controllers;

use App\Models\ClassChangeLog;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\Setting;
use App\Notifications\RefundIssuedNotification;
use App\Services\BlockchainAuditService;
use App\Services\EnrollmentQueueService;
use App\Services\FireflyService;
use App\Services\PortalNotificationService;
use App\Services\PromotionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class EnrollmentController extends Controller
{
    public function __construct(
        protected FireflyService $firefly,
        protected BlockchainAuditService $blockchainAudit,
        protected EnrollmentQueueService $enrollmentQueue,
        protected PortalNotificationService $notificationService,
        protected PromotionService $promotionService,
    ) {
    }

    public function enroll(Request $request, Course $course)
    {
        if ($course->status !== 'published') {
            return back()->with('error', 'KhÃ³a há»c hiá»‡n khÃ´ng kháº£ dá»¥ng.');
        }

        $class = $this->resolveSelectedClass($request, $course);

        if (! $class) {
            $message = $course->isOffline()
                ? 'Vui lÃ²ng chá»n má»™t Ä‘á»£t há»c Ä‘ang má»Ÿ Ä‘Äƒng kÃ½.'
                : 'KhÃ³a há»c nÃ y hiá»‡n chÆ°a má»Ÿ Ä‘Äƒng kÃ½.';

            return back()->withInput()->with('error', $message);
        }

        if ($class->status !== 'active') {
            return back()->withInput()->with('error', $course->isOffline()
                ? 'Äá»£t há»c nÃ y hiá»‡n chÆ°a má»Ÿ Ä‘Äƒng kÃ½.'
                : 'KhÃ³a há»c nÃ y hiá»‡n chÆ°a má»Ÿ Ä‘Äƒng kÃ½.');
        }

        if ($course->isOffline()) {
            $this->enrollmentQueue->syncClassQueue($class);
            $class = $class->fresh();
        }

        $existing = CourseEnrollment::where('user_id', Auth::id())
            ->forCourse($course)
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->latest('id')
            ->first();

        if ($existing) {
            $currentClassName = $existing->courseClass->name ?? null;

            if ($existing->hasActiveSeatHold()) {
                $deadline = optional($existing->seat_hold_expires_at)->format('d/m/Y H:i');

                return back()->with('error', $currentClassName
                    ? 'Báº¡n Ä‘ang Ä‘Æ°á»£c giá»¯ chá»— 24h á»Ÿ Ä‘á»£t há»c ' . $currentClassName . ' Ä‘áº¿n ' . $deadline . '. Vui lÃ²ng xÃ¡c nháº­n Ä‘Äƒng kÃ½ trÆ°á»›c khi giá»¯ chá»— háº¿t háº¡n.'
                    : 'Báº¡n Ä‘ang cÃ³ má»™t chá»— giá»¯ táº¡m 24h. Vui lÃ²ng xÃ¡c nháº­n Ä‘Äƒng kÃ½ trÆ°á»›c khi háº¿t háº¡n.');
            }

            if ($existing->isWaitlisted()) {
                $position = $existing->waitlist_position;
                $positionLabel = $position ? ' á»Ÿ vá»‹ trÃ­ ' . $position : '';

                return back()->with('error', $currentClassName
                    ? 'Báº¡n Ä‘Ã£ á»Ÿ trong hÃ ng chá»' . $positionLabel . ' cho Ä‘á»£t há»c ' . $currentClassName . '.'
                    : 'Báº¡n Ä‘Ã£ á»Ÿ trong hÃ ng chá» cho khÃ³a há»c nÃ y.');
            }

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

        if ($course->isOffline() && $class->is_full) {
            $waitlistEnrollment = $this->enrollmentQueue->joinWaitlist(Auth::id(), $class, 'waitlist_joined');
            $this->notificationService->notifyWaitlistJoined($waitlistEnrollment);
            $position = $waitlistEnrollment->waitlist_position;
            $positionLabel = $position ? ' Vá»‹ trÃ­ hiá»‡n táº¡i cá»§a báº¡n lÃ  #' . $position . '.' : '';

            return back()->with('success', 'Äá»£t há»c nÃ y Ä‘Ã£ Ä‘áº§y, há»‡ thá»‘ng Ä‘Ã£ Ä‘Æ°a báº¡n vÃ o hÃ ng chá».' . $positionLabel . ' Khi cÃ³ chá»— trá»‘ng, báº¡n sáº½ Ä‘Æ°á»£c giá»¯ chá»— trong 24 giá» Ä‘á»ƒ xÃ¡c nháº­n Ä‘Äƒng kÃ½.');
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
                $enrollment = $this->storeEnrollmentRequest(Auth::id(), $class, [
                    'base_price' => $this->promotionService->resolveBasePrice($course, $class),
                    'discount_amount' => 0,
                    'final_price' => 0,
                    'discount_snapshot' => [
                        'base_price' => $this->promotionService->resolveBasePrice($course, $class),
                        'discount_amount' => 0,
                        'payable_amount' => 0,
                        'applied_items' => [],
                    ],
                ]);
                $enrollment->approve();
                $this->sendRegistrationSuccessMail($enrollment);

                return back()->with('success', 'ÄÄƒng kÃ½ thÃ nh cÃ´ng, báº¡n cÃ³ thá»ƒ há»c ngay.');
            }
        }

        $pricing = $this->promotionService->preview(
            Auth::user(),
            $course,
            $class,
            $request->input('discount_code')
        );

        if (filled((string) $request->input('discount_code')) && ($pricing['voucher_error'] ?? null)) {
            return back()->withInput()->with('error', $pricing['voucher_error']);
        }

        $payableAmount = (float) ($pricing['payable_amount'] ?? 0);
        $requiresManualApproval = $course->requiresManualApproval();
        $walletPaid = false;

        if ($payableAmount > 0) {
            $requestedMethod = (string) $request->input('payment_method', 'wallet');

            if ($requestedMethod !== 'wallet') {
                return back()
                    ->withInput()
                    ->with('error', 'KhÃ³a há»c tráº£ phÃ­ hiá»‡n chá»‰ há»— trá»£ thanh toÃ¡n báº±ng sá»‘ dÆ° vÃ­. Vui lÃ²ng náº¡p tiá»n vÃ o vÃ­ rá»“i Ä‘Äƒng kÃ½ láº¡i.');
            }

            $purchaseResult = $this->processWalletCoursePurchase(
                $request,
                $course,
                $class,
                $pricing,
                $requiresManualApproval,
                'Thanh toÃ¡n báº±ng vÃ­ ná»™i bá»™, chá» admin duyá»‡t Ä‘Äƒng kÃ½.',
                'Thanh toÃ¡n báº±ng vÃ­ ná»™i bá»™.'
            );

            if (isset($purchaseResult['error'])) {
                $response = back()->withInput()->with('error', $purchaseResult['error']);

                if (! empty($purchaseResult['required_topup'])) {
                    $response->with('required_topup', true);
                }

                return $response;
            }

            $walletPaid = true;
        } elseif ((float) ($pricing['base_price'] ?? 0) > 0 && (float) ($pricing['discount_amount'] ?? 0) > 0) {
            $this->recordPromotionOnlyPayment($course, $class, $pricing, $requiresManualApproval);
        }

        $enrollment = $this->storeEnrollmentRequest(
            Auth::id(),
            $class,
            $this->buildPricingAttributes($pricing)
        );

        if ($requiresManualApproval) {
            $this->sendRegistrationSuccessMail($enrollment, $walletPaid);

            return back()->with('success', $walletPaid
                ? 'Thanh toÃ¡n báº±ng vÃ­ thÃ nh cÃ´ng. YÃªu cáº§u Ä‘Äƒng kÃ½ cá»§a báº¡n Ä‘ang chá» admin duyá»‡t.'
                : 'ÄÄƒng kÃ½ thÃ nh cÃ´ng, vui lÃ²ng chá» admin duyá»‡t.');
        }

        $enrollment->approve();
        $this->sendRegistrationSuccessMail($enrollment, $walletPaid);

        return back()->with('success', $walletPaid
            ? 'Thanh toÃ¡n báº±ng vÃ­ thÃ nh cÃ´ng, báº¡n cÃ³ thá»ƒ há»c ngay.'
            : 'ÄÄƒng kÃ½ thÃ nh cÃ´ng, báº¡n cÃ³ thá»ƒ há»c ngay.');
    }

    public function confirmSeatHold(Request $request, Course $course)
    {
        $enrollment = CourseEnrollment::where('user_id', Auth::id())
            ->forCourse($course)
            ->pending()
            ->latest('id')
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'KhÃ´ng tÃ¬m tháº¥y yÃªu cáº§u giá»¯ chá»— phÃ¹ há»£p.');
        }

        $class = $enrollment->courseClass;

        if (! $course->isOffline() || ! $class) {
            return back()->with('error', 'YÃªu cáº§u nÃ y khÃ´ng Ã¡p dá»¥ng giá»¯ chá»— 24h.');
        }

        $this->enrollmentQueue->syncClassQueue($class);
        $enrollment->refresh();

        if (! $enrollment->hasActiveSeatHold()) {
            return back()->with('error', 'Thá»i gian giá»¯ chá»— Ä‘Ã£ háº¿t hoáº·c chÆ°a tá»›i lÆ°á»£t cá»§a báº¡n.');
        }

        $class = $enrollment->courseClass?->fresh();
        $pricing = $this->promotionService->preview(
            Auth::user(),
            $course,
            $class,
            $request->input('discount_code')
        );

        if (filled((string) $request->input('discount_code')) && ($pricing['voucher_error'] ?? null)) {
            return back()->withInput()->with('error', $pricing['voucher_error']);
        }

        $payableAmount = (float) ($pricing['payable_amount'] ?? 0);
        $requiresManualApproval = $course->requiresManualApproval();
        $walletPaid = false;

        if ($payableAmount > 0) {
            $purchaseResult = $this->processWalletCoursePurchase(
                $request,
                $course,
                $class,
                $pricing,
                $requiresManualApproval,
                'Thanh toÃ¡n báº±ng vÃ­ ná»™i bá»™ sau khi Ä‘Æ°á»£c giá»¯ chá»— 24h, chá» admin duyá»‡t Ä‘Äƒng kÃ½.',
                'Thanh toÃ¡n báº±ng vÃ­ ná»™i bá»™ sau khi xÃ¡c nháº­n giá»¯ chá»— 24h.'
            );

            if (isset($purchaseResult['error'])) {
                $response = back()->withInput()->with('error', $purchaseResult['error']);

                if (! empty($purchaseResult['required_topup'])) {
                    $response->with('required_topup', true);
                }

                return $response;
            }

            $walletPaid = true;
        } elseif ((float) ($pricing['base_price'] ?? 0) > 0 && (float) ($pricing['discount_amount'] ?? 0) > 0) {
            $this->recordPromotionOnlyPayment($course, $class, $pricing, $requiresManualApproval);
        }

        $enrollment = $this->storeEnrollmentRequest(
            Auth::id(),
            $class,
            $this->buildPricingAttributes($pricing, [
                'notes' => $walletPaid ? 'seat_hold_confirmed_with_wallet' : 'seat_hold_confirmed',
            ])
        );

        if ($requiresManualApproval) {
            $this->sendRegistrationSuccessMail($enrollment, $walletPaid);

            return back()->with('success', 'Báº¡n Ä‘Ã£ xÃ¡c nháº­n giá»¯ chá»— thÃ nh cÃ´ng. YÃªu cáº§u Ä‘Äƒng kÃ½ Ä‘ang chá» admin duyá»‡t.');
        }

        $enrollment->approve();
        $this->sendRegistrationSuccessMail($enrollment, $walletPaid);

        return back()->with('success', 'Báº¡n Ä‘Ã£ xÃ¡c nháº­n giá»¯ chá»— thÃ nh cÃ´ng vÃ  cÃ³ thá»ƒ báº¯t Ä‘áº§u há»c ngay.');
    }

    public function unenroll(Course $course)
    {
        $enrollment = CourseEnrollment::where('user_id', Auth::id())
            ->forCourse($course)
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->latest('id')
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'BÃ¡ÂºÂ¡n chÃ†Â°a Ã„â€˜Ã„Æ’ng kÃƒÂ½ khÃƒÂ³a hÃ¡Â»Âc nÃƒÂ y.');
        }

        if ($enrollment->isCompleted()) {
            return back()->with('error', 'KhÃƒÂ´ng thÃ¡Â»Æ’ hÃ¡Â»Â§y Ã„â€˜Ã„Æ’ng kÃƒÂ½ Ã„â€˜ÃƒÂ£ hoÃƒÂ n thÃƒÂ nh hoÃ¡ÂºÂ·c bÃ¡Â»â€¹ tÃ¡Â»Â« chÃ¡Â»â€˜i.');
        }

        $wasPending = $enrollment->isPending();
        $wasWaitlisted = $enrollment->isWaitlisted();
        $hadSeatHold = $enrollment->hasActiveSeatHold();
        $class = $enrollment->class;
        $now = now();

        if ($class && $class->start_date && $now->lt($class->start_date) && ! $wasWaitlisted && ! $hadSeatHold) {
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

        $enrollment->cancel($hadSeatHold ? 'student_cancelled_held_seat' : ($wasWaitlisted ? 'student_left_waitlist' : 'student_cancelled'));

        if ($class && $course->isOffline()) {
            $this->enrollmentQueue->syncClassQueue($class);
        }

        if ($hadSeatHold) {
            return back()->with('success', 'BÃ¡ÂºÂ¡n Ã„â€˜ÃƒÂ£ hÃ¡Â»Â§y giÃ¡Â»Â¯ chÃ¡Â»â€” 24h. HÃ¡Â»â€¡ thÃ¡Â»â€˜ng sÃ¡ÂºÂ½ chuyÃ¡Â»Æ’n chÃ¡Â»â€” cho ngÃ†Â°Ã¡Â»Âi kÃ¡ÂºÂ¿ tiÃ¡ÂºÂ¿p trong hÃƒÂ ng chÃ¡Â»Â.');
        }

        if ($wasWaitlisted) {
            return back()->with('success', 'BÃ¡ÂºÂ¡n Ã„â€˜ÃƒÂ£ rÃ¡Â»Âi khÃ¡Â»Âi hÃƒÂ ng chÃ¡Â»Â cÃ¡Â»Â§a Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc nÃƒÂ y.');
        }

        return back()->with('success', $wasPending
            ? 'Ã„ÂÃƒÂ£ hÃ¡Â»Â§y yÃƒÂªu cÃ¡ÂºÂ§u Ã„â€˜Ã„Æ’ng kÃƒÂ½.'
            : 'Ã„ÂÃƒÂ£ hÃ¡Â»Â§y Ã„â€˜Ã„Æ’ng kÃƒÂ½ khÃƒÂ³a hÃ¡Â»Âc.');
    }

    public function changeClass(Request $request, Course $course, CourseEnrollment $enrollment)
    {
        abort_unless($enrollment->user_id === Auth::id(), 403);

        $enrollment->loadMissing(['courseClass.course']);

        abort_unless((int) optional($enrollment->courseClass)->course_id === (int) $course->id, 404);

        if ($enrollment->isCompleted() || $enrollment->isRejected()) {
            return back()->with('error', 'KhÃƒÂ´ng thÃ¡Â»Æ’ Ã„â€˜Ã¡Â»â€¢i Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc cho Ã„â€˜Ã„Æ’ng kÃƒÂ½ nÃƒÂ y.');
        }

        if ($enrollment->isWaitlisted() || $enrollment->hasActiveSeatHold()) {
            return back()->with('error', 'Vui lÃƒÂ²ng xÃƒÂ¡c nhÃ¡ÂºÂ­n hoÃ¡ÂºÂ·c hÃ¡Â»Â§y yÃƒÂªu cÃ¡ÂºÂ§u hÃƒÂ ng chÃ¡Â»Â/giÃ¡Â»Â¯ chÃ¡Â»â€” hiÃ¡Â»â€¡n tÃ¡ÂºÂ¡i trÃ†Â°Ã¡Â»â€ºc khi Ã„â€˜Ã¡Â»â€¢i Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc.');
        }

        if ((string) Setting::get('allow_class_change', '1') === '0') {
            return back()->with('error', 'ChÃ¡Â»Â©c nÃ„Æ’ng Ã„â€˜Ã¡Â»â€¢i Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc hiÃ¡Â»â€¡n Ã„â€˜ang bÃ¡Â»â€¹ khÃƒÂ³a.');
        }

        if ($course->isOnline()) {
            return back()->with('error', 'KhÃƒÂ³a hÃ¡Â»Âc trÃ¡Â»Â±c tuyÃ¡ÂºÂ¿n khÃƒÂ´ng ÃƒÂ¡p dÃ¡Â»Â¥ng Ã„â€˜Ã¡Â»â€¢i Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc.');
        }

        $validated = $request->validate([
            'new_class_id' => 'required|exists:classes,id',
        ]);

        $currentClass = $enrollment->courseClass;
        $newClass = $course->classes()
            ->whereKey($validated['new_class_id'])
            ->first();

        if (! $newClass) {
            return back()->with('error', 'Ã„ÂÃ¡Â»Â£t hÃ¡Â»Âc mÃ¡Â»â€ºi khÃƒÂ´ng thuÃ¡Â»â„¢c khÃƒÂ³a hÃ¡Â»Âc nÃƒÂ y.');
        }

        if ($currentClass && (int) $currentClass->id === (int) $newClass->id) {
            return back()->with('error', 'BÃ¡ÂºÂ¡n Ã„â€˜ang Ã¡Â»Å¸ Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc nÃƒÂ y rÃ¡Â»â€œi.');
        }

        if ($newClass->status !== 'active') {
            return back()->with('error', 'Ã„ÂÃ¡Â»Â£t hÃ¡Â»Âc mÃ¡Â»â€ºi hiÃ¡Â»â€¡n chÃ†Â°a mÃ¡Â»Å¸ Ã„â€˜Ã„Æ’ng kÃƒÂ½.');
        }

        $this->enrollmentQueue->syncClassQueue($newClass);
        $newClass = $newClass->fresh();

        if ($newClass->is_full) {
            return back()->with('error', 'Ã„ÂÃ¡Â»Â£t hÃ¡Â»Âc mÃ¡Â»â€ºi Ã„â€˜ÃƒÂ£ Ã„â€˜Ã¡Â»Â§ sÃ¡Â»â€˜ lÃ†Â°Ã¡Â»Â£ng hÃ¡Â»Âc viÃƒÂªn.');
        }

        $now = now();

        if ($currentClass?->start_date && $now->greaterThanOrEqualTo($currentClass->start_date->copy()->startOfDay())) {
            return back()->with('error', 'KhÃƒÂ´ng thÃ¡Â»Æ’ Ã„â€˜Ã¡Â»â€¢i Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc sau khi Ã„â€˜Ã¡Â»Â£t hiÃ¡Â»â€¡n tÃ¡ÂºÂ¡i Ã„â€˜ÃƒÂ£ bÃ¡ÂºÂ¯t Ã„â€˜Ã¡ÂºÂ§u.');
        }

        if ($newClass->start_date && $now->greaterThanOrEqualTo($newClass->start_date->copy()->startOfDay())) {
            return back()->with('error', 'KhÃƒÂ´ng thÃ¡Â»Æ’ chuyÃ¡Â»Æ’n sang Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc Ã„â€˜ÃƒÂ£ bÃ¡ÂºÂ¯t Ã„â€˜Ã¡ÂºÂ§u.');
        }

        $deadlineDays = (int) Setting::get('class_change_deadline_days', '0');
        if ($deadlineDays > 0 && $newClass->start_date) {
            $deadline = $newClass->start_date->copy()->startOfDay()->subDays($deadlineDays);

            if ($now->greaterThan($deadline)) {
                return back()->with('error', 'Ã„ÂÃƒÂ£ quÃƒÂ¡ hÃ¡ÂºÂ¡n Ã„â€˜Ã¡Â»â€¢i Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc cho Ã„â€˜Ã¡Â»Â£t bÃ¡ÂºÂ¡n chÃ¡Â»Ân.');
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

        if ($currentClass) {
            $this->enrollmentQueue->syncClassQueue($currentClass);
        }

        return back()->with('success', 'Ã„ÂÃƒÂ£ chuyÃ¡Â»Æ’n sang Ã„â€˜Ã¡Â»Â£t hÃ¡Â»Âc ' . $newClass->name . '.');
    }

    private function processWalletCoursePurchase(
        Request $request,
        Course $course,
        CourseClass $class,
        array $pricing,
        bool $requiresManualApproval,
        string $pendingNote,
        string $completedNote
    ): array {
        $wallet = Auth::user()->getOrCreateWallet();
        $payableAmount = (float) ($pricing['payable_amount'] ?? 0);

        if ((float) $wallet->balance < $payableAmount) {
            return [
                'error' => 'Sá»‘ dÆ° vÃ­ hiá»‡n chÆ°a Ä‘á»§. Vui lÃ²ng náº¡p tiá»n vÃ o vÃ­ Ä‘á»ƒ mua khÃ³a há»c.',
                'required_topup' => true,
            ];
        }

        $metadata = [
            'course_id' => $course->id,
            'class_id' => $class->id,
            'base_price' => $pricing['base_price'] ?? $payableAmount,
            'discount_amount' => $pricing['discount_amount'] ?? 0,
            'payable_amount' => $payableAmount,
            'discount_code_id' => $pricing['voucher']?->id,
            'discount_snapshot' => $this->promotionService->buildSnapshot($pricing),
        ];

        $transaction = $wallet->charge($payableAmount, $metadata);

        if (! $transaction) {
            return [
                'error' => 'KhÃ´ng thá»ƒ trá»« tiá»n tá»« vÃ­ vÃ o lÃºc nÃ y. Vui lÃ²ng thá»­ láº¡i sau.',
            ];
        }

        $purchaseReference = $transaction->reference ?: ('COURSE-' . $course->id . '-' . Auth::id() . '-' . now()->format('YmdHis'));
        $fireflyResult = null;
        $auditResponse = null;

        try {
            $platformIdentity = $this->firefly->getPlatformIdentity();
            $fireflyResult = $this->firefly->transfer(
                $wallet->firefly_identity,
                $platformIdentity,
                $payableAmount,
                [
                    'reference' => $purchaseReference,
                    'data' => [
                        'type' => 'course_purchase',
                        'wallet_transaction_id' => $transaction->id,
                        'course_id' => $course->id,
                        'class_id' => $class->id,
                        'user_id' => Auth::id(),
                        'amount' => $payableAmount,
                        'discount_amount' => $pricing['discount_amount'] ?? 0,
                    ],
                ]
            );

            $auditResponse = $this->blockchainAudit->record('wallet.course_purchase', [
                'wallet_transaction_id' => $transaction->id,
                'course_id' => $course->id,
                'course_title' => $course->title,
                'class_id' => $class->id,
                'class_name' => $class->name,
                'user_id' => Auth::id(),
                'amount' => $payableAmount,
                'discount_amount' => $pricing['discount_amount'] ?? 0,
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
        } catch (\Throwable $exception) {
            report($exception);
        }

        $transaction->update([
            'reference' => $purchaseReference,
            'metadata' => array_merge($transaction->metadata ?? [], array_filter([
                'firefly' => $fireflyResult,
                'blockchain_audit' => $auditResponse,
            ])),
        ]);

        Payment::create([
            'user_id' => Auth::id(),
            'class_id' => $class->id,
            'amount' => $payableAmount,
            'base_amount' => $pricing['base_price'] ?? $payableAmount,
            'discount_amount' => $pricing['discount_amount'] ?? 0,
            'discount_code_id' => $pricing['voucher']?->id,
            'method' => 'wallet',
            'status' => 'completed',
            'paid_at' => now(),
            'reference' => $purchaseReference,
            'notes' => $requiresManualApproval ? $pendingNote : $completedNote,
            'metadata' => $this->promotionService->buildSnapshot($pricing),
        ]);

        return [
            'wallet_paid' => true,
        ];
    }

    private function sendRegistrationSuccessMail(CourseEnrollment $enrollment, bool $walletPaid = false): void
    {
        try {
            if ($enrollment->isPending()) {
                $this->notificationService->notifyEnrollmentReceived($enrollment, $walletPaid);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }
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
            'waitlist_joined_at' => null,
            'waitlist_promoted_at' => null,
            'seat_hold_expires_at' => null,
            'base_price' => null,
            'discount_amount' => 0,
            'final_price' => null,
            'discount_code_id' => null,
            'discount_snapshot' => null,
            'completed_at' => null,
        ], $attributes));

        $enrollment->save();

        return $enrollment;
    }

    private function buildPricingAttributes(array $pricing, array $extra = []): array
    {
        return array_merge([
            'base_price' => $pricing['base_price'] ?? null,
            'discount_amount' => $pricing['discount_amount'] ?? 0,
            'final_price' => $pricing['payable_amount'] ?? null,
            'discount_code_id' => $pricing['voucher']?->id,
            'discount_snapshot' => $this->promotionService->buildSnapshot($pricing),
        ], $extra);
    }

    private function recordPromotionOnlyPayment(Course $course, CourseClass $class, array $pricing, bool $requiresManualApproval): void
    {
        Payment::create([
            'user_id' => Auth::id(),
            'class_id' => $class->id,
            'amount' => 0,
            'base_amount' => $pricing['base_price'] ?? 0,
            'discount_amount' => $pricing['discount_amount'] ?? 0,
            'discount_code_id' => $pricing['voucher']?->id,
            'method' => 'promotion',
            'status' => 'completed',
            'paid_at' => now(),
            'reference' => 'PROMO-' . $course->id . '-' . Auth::id() . '-' . now()->format('YmdHis'),
            'notes' => $requiresManualApproval
                ? 'Miá»…n phÃ­ sau khi Ã¡p dá»¥ng khuyáº¿n mÃ£i, chá» admin duyá»‡t Ä‘Äƒng kÃ½.'
                : 'Miá»…n phÃ­ sau khi Ã¡p dá»¥ng khuyáº¿n mÃ£i.',
            'metadata' => $this->promotionService->buildSnapshot($pricing),
        ]);
    }
}