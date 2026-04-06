<?php

namespace App\Http\Controllers;

use App\Models\ClassChangeLog;
use App\Models\Course;
use App\Models\CourseClass;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\Setting;
use App\Notifications\RefundIssuedNotification;
use App\Services\ApplicationLifecycleBlockchainService;
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
        protected ApplicationLifecycleBlockchainService $lifecycleBlockchain,
    ) {
    }

    public function enroll(Request $request, Course $course)
    {
        if ($course->status !== 'published') {
            return back()->with('error', 'Khóa học hiện không khả dụng.');
        }

        $class = $this->resolveSelectedClass($request, $course);

        if (! $class) {
            $message = $course->isOffline()
                ? 'Vui lòng chọn một đợt học đang mở đăng ký.'
                : 'Khóa học này hiện chưa mở đăng ký.';

            return back()->withInput()->with('error', $message);
        }

        if ($class->status !== 'active') {
            return back()->withInput()->with('error', $course->isOffline()
                ? 'Đợt học này hiện chưa mở đăng ký.'
                : 'Khóa học này hiện chưa mở đăng ký.');
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
                    ? 'Bạn đang được giữ chỗ 24h ở đợt học ' . $currentClassName . ' đến ' . $deadline . '. Vui lòng xác nhận đăng ký trước khi giữ chỗ hết hạn.'
                    : 'Bạn đang có một chỗ giữ tạm 24h. Vui lòng xác nhận đăng ký trước khi hết hạn.');
            }

            if ($existing->isWaitlisted()) {
                $position = $existing->waitlist_position;
                $positionLabel = $position ? ' ở vị trí ' . $position : '';

                return back()->with('error', $currentClassName
                    ? 'Bạn đã ở trong hàng chờ' . $positionLabel . ' cho đợt học ' . $currentClassName . '.'
                    : 'Bạn đã ở trong hàng chờ cho khóa học này.');
            }

            if ($existing->isPending()) {
                return back()->with('error', $currentClassName
                    ? 'Bạn đã có yêu cầu đăng ký chờ duyệt ở đợt học ' . $currentClassName . '.'
                    : 'Bạn đã có yêu cầu đăng ký khóa học này đang chờ duyệt.');
            }

            if ($existing->isApproved() || $existing->isCompleted()) {
                return back()->with('error', $currentClassName
                    ? 'Bạn đã đăng ký khóa học này ở đợt học ' . $currentClassName . ' rồi.'
                    : 'Bạn đã đăng ký khóa học này rồi.');
            }
        }

        if ($course->isOffline() && $class->is_full) {
            $waitlistEnrollment = $this->enrollmentQueue->joinWaitlist(
                Auth::id(),
                $class,
                'waitlist_joined',
                $this->lifecycleContext($request),
                ['source' => 'student_portal', 'trigger' => 'course_checkout']
            );
            $this->notificationService->notifyWaitlistJoined($waitlistEnrollment);
            $position = $waitlistEnrollment->waitlist_position;
            $positionLabel = $position ? ' Vị trí hiện tại của bạn là #' . $position . '.' : '';

            return back()->with('success', 'Đợt học này đã đầy, hệ thống đã đưa bạn vào hàng chờ.' . $positionLabel . ' Khi có chỗ trống, bạn sẽ được giữ chỗ trong 24 giờ để xác nhận đăng ký.');
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
                $this->syncEnrollmentCreationLifecycle($enrollment, $request, [
                    'source' => 'student_portal',
                    'trigger' => 'series_unlock',
                ]);
                $this->syncEnrollmentApprovedLifecycle($enrollment, $request, [
                    'source' => 'student_portal',
                    'trigger' => 'series_unlock',
                ]);
                $this->sendRegistrationSuccessMail($enrollment);

                return back()->with('success', 'Đăng ký thành công, bạn có thể học ngay.');
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
        $paymentRecord = null;

        if ($payableAmount > 0) {
            $requestedMethod = (string) $request->input('payment_method', 'wallet');

            if ($requestedMethod !== 'wallet') {
                return back()
                    ->withInput()
                    ->with('error', 'Khóa học trả phí hiện chỉ hỗ trợ thanh toán bằng số dư ví. Vui lòng nạp tiền vào ví rồi đăng ký lại.');
            }

            $purchaseResult = $this->processWalletCoursePurchase(
                $request,
                $course,
                $class,
                $pricing,
                $requiresManualApproval,
                'Thanh toán bằng ví nội bộ, chờ admin duyệt đăng ký.',
                'Thanh toán bằng ví nội bộ.'
            );

            if (isset($purchaseResult['error'])) {
                $response = back()->withInput()->with('error', $purchaseResult['error']);

                if (! empty($purchaseResult['required_topup'])) {
                    $response->with('required_topup', true);
                }

                return $response;
            }

            $walletPaid = true;
            $paymentRecord = $purchaseResult['payment'] ?? null;
        } elseif ((float) ($pricing['base_price'] ?? 0) > 0 && (float) ($pricing['discount_amount'] ?? 0) > 0) {
            $paymentRecord = $this->recordPromotionOnlyPayment($course, $class, $pricing, $requiresManualApproval);
        }

        $enrollment = $this->storeEnrollmentRequest(
            Auth::id(),
            $class,
            $this->buildPricingAttributes($pricing)
        );

        $this->syncEnrollmentCreationLifecycle($enrollment, $request, [
            'source' => 'student_portal',
            'trigger' => 'course_checkout',
        ]);
        if ($paymentRecord) {
            $this->syncPaymentLifecycle($paymentRecord, $enrollment, $request, [
                'source' => 'student_portal',
                'trigger' => 'course_checkout',
            ]);
        }

        if ($requiresManualApproval) {
            $this->sendRegistrationSuccessMail($enrollment, $walletPaid);

            return back()->with('success', $walletPaid
                ? 'Thanh toán bằng ví thành công. Yêu cầu đăng ký của bạn đang chờ admin duyệt.'
                : 'Đăng ký thành công, vui lòng chờ admin duyệt.');
        }

        $enrollment->approve();
        $this->syncEnrollmentApprovedLifecycle($enrollment, $request, [
            'source' => 'student_portal',
            'trigger' => 'course_checkout',
        ]);
        $this->sendRegistrationSuccessMail($enrollment, $walletPaid);

        return back()->with('success', $walletPaid
            ? 'Thanh toán bằng ví thành công, bạn có thể học ngay.'
            : 'Đăng ký thành công, bạn có thể học ngay.');
    }

    public function confirmSeatHold(Request $request, Course $course)
    {
        $enrollment = CourseEnrollment::where('user_id', Auth::id())
            ->forCourse($course)
            ->pending()
            ->latest('id')
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'Không tìm thấy yêu cầu giữ chỗ phù hợp.');
        }

        $class = $enrollment->courseClass;

        if (! $course->isOffline() || ! $class) {
            return back()->with('error', 'Yêu cầu này không áp dụng giữ chỗ 24h.');
        }

        $this->enrollmentQueue->syncClassQueue($class);
        $enrollment->refresh();

        if (! $enrollment->hasActiveSeatHold()) {
            return back()->with('error', 'Thời gian giữ chỗ đã hết hoặc chưa tới lượt của bạn.');
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
        $paymentRecord = null;

        if ($payableAmount > 0) {
            $purchaseResult = $this->processWalletCoursePurchase(
                $request,
                $course,
                $class,
                $pricing,
                $requiresManualApproval,
                'Thanh toán bằng ví nội bộ sau khi được giữ chỗ 24h, chờ admin duyệt đăng ký.',
                'Thanh toán bằng ví nội bộ sau khi xác nhận giữ chỗ 24h.'
            );

            if (isset($purchaseResult['error'])) {
                $response = back()->withInput()->with('error', $purchaseResult['error']);

                if (! empty($purchaseResult['required_topup'])) {
                    $response->with('required_topup', true);
                }

                return $response;
            }

            $walletPaid = true;
            $paymentRecord = $purchaseResult['payment'] ?? null;
        } elseif ((float) ($pricing['base_price'] ?? 0) > 0 && (float) ($pricing['discount_amount'] ?? 0) > 0) {
            $paymentRecord = $this->recordPromotionOnlyPayment($course, $class, $pricing, $requiresManualApproval);
        }

        $enrollment = $this->storeEnrollmentRequest(
            Auth::id(),
            $class,
            $this->buildPricingAttributes($pricing, [
                'notes' => $walletPaid ? 'seat_hold_confirmed_with_wallet' : 'seat_hold_confirmed',
            ])
        );

        $this->syncEnrollmentCreationLifecycle($enrollment, $request, [
            'source' => 'seat_hold_confirmation',
            'trigger' => 'seat_hold_confirmation',
        ]);
        $this->lifecycleBlockchain->seatHoldConfirmed($enrollment, $this->lifecycleContext($request), [
            'source' => 'seat_hold_confirmation',
            'trigger' => 'seat_hold_confirmation',
            'wallet_paid' => $walletPaid,
        ]);
        if ($paymentRecord) {
            $this->syncPaymentLifecycle($paymentRecord, $enrollment, $request, [
                'source' => 'seat_hold_confirmation',
                'trigger' => 'seat_hold_confirmation',
            ]);
        }

        if ($requiresManualApproval) {
            $this->sendRegistrationSuccessMail($enrollment, $walletPaid);

            return back()->with('success', 'Bạn đã xác nhận giữ chỗ thành công. Yêu cầu đăng ký đang chờ admin duyệt.');
        }

        $enrollment->approve();
        $this->syncEnrollmentApprovedLifecycle($enrollment, $request, [
            'source' => 'seat_hold_confirmation',
            'trigger' => 'seat_hold_confirmation',
        ]);
        $this->sendRegistrationSuccessMail($enrollment, $walletPaid);

        return back()->with('success', 'Bạn đã xác nhận giữ chỗ thành công và có thể bắt đầu học ngay.');
    }

    public function unenroll(Request $request, Course $course)
    {
        $enrollment = CourseEnrollment::where('user_id', Auth::id())
            ->forCourse($course)
            ->whereIn('status', ['pending', 'approved', 'completed'])
            ->latest('id')
            ->first();

        if (! $enrollment) {
            return back()->with('error', 'Bạn chưa đăng ký khóa học này.');
        }

        if ($enrollment->isCompleted()) {
            return back()->with('error', 'Không thể hủy đăng ký đã hoàn thành hoặc bị từ chối.');
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
        $this->lifecycleBlockchain->cancelled($enrollment->fresh(['user', 'courseClass.course']), $this->lifecycleContext($request), [
            'source' => 'student_portal',
            'trigger' => 'unenroll',
            'was_waitlisted' => $wasWaitlisted,
            'had_seat_hold' => $hadSeatHold,
        ]);

        if ($class && $course->isOffline()) {
            $this->enrollmentQueue->syncClassQueue($class);
        }

        if ($hadSeatHold) {
            return back()->with('success', 'Bạn đã hủy giữ chỗ 24h. Hệ thống sẽ chuyển chỗ cho người kế tiếp trong hàng chờ.');
        }

        if ($wasWaitlisted) {
            return back()->with('success', 'Bạn đã rời khỏi hàng chờ của đợt học này.');
        }

        return back()->with('success', $wasPending
            ? 'Đã hủy yêu cầu đăng ký.'
            : 'Đã hủy đăng ký khóa học.');
    }

    public function changeClass(Request $request, Course $course, CourseEnrollment $enrollment)
    {
        abort_unless($enrollment->user_id === Auth::id(), 403);

        $enrollment->loadMissing(['courseClass.course']);

        abort_unless((int) optional($enrollment->courseClass)->course_id === (int) $course->id, 404);

        if ($enrollment->isCompleted() || $enrollment->isRejected()) {
            return back()->with('error', 'Không thể đổi đợt học cho đăng ký này.');
        }

        if ($enrollment->isWaitlisted() || $enrollment->hasActiveSeatHold()) {
            return back()->with('error', 'Vui lòng xác nhận hoặc hủy yêu cầu hàng chờ/giữ chỗ hiện tại trước khi đổi đợt học.');
        }

        if ((string) Setting::get('allow_class_change', '1') === '0') {
            return back()->with('error', 'Chức năng đổi đợt học hiện đang bị khóa.');
        }

        if ($course->isOnline()) {
            return back()->with('error', 'Khóa học trực tuyến không áp dụng đổi đợt học.');
        }

        $validated = $request->validate([
            'new_class_id' => 'required|exists:classes,id',
        ]);

        $currentClass = $enrollment->courseClass;
        $newClass = $course->classes()
            ->whereKey($validated['new_class_id'])
            ->first();

        if (! $newClass) {
            return back()->with('error', 'Đợt học mới không thuộc khóa học này.');
        }

        if ($currentClass && (int) $currentClass->id === (int) $newClass->id) {
            return back()->with('error', 'Bạn đang ở đợt học này rồi.');
        }

        if ($newClass->status !== 'active') {
            return back()->with('error', 'Đợt học mới hiện chưa mở đăng ký.');
        }

        $this->enrollmentQueue->syncClassQueue($newClass);
        $newClass = $newClass->fresh();

        if ($newClass->is_full) {
            return back()->with('error', 'Đợt học mới đã đủ số lượng học viên.');
        }

        $now = now();

        if ($currentClass?->start_date && $now->greaterThanOrEqualTo($currentClass->start_date->copy()->startOfDay())) {
            return back()->with('error', 'Không thể đổi đợt học sau khi đợt hiện tại đã bắt đầu.');
        }

        if ($newClass->start_date && $now->greaterThanOrEqualTo($newClass->start_date->copy()->startOfDay())) {
            return back()->with('error', 'Không thể chuyển sang đợt học đã bắt đầu.');
        }

        $deadlineDays = (int) Setting::get('class_change_deadline_days', '0');
        if ($deadlineDays > 0 && $newClass->start_date) {
            $deadline = $newClass->start_date->copy()->startOfDay()->subDays($deadlineDays);

            if ($now->greaterThan($deadline)) {
                return back()->with('error', 'Đã quá hạn đổi đợt học cho đợt bạn chọn.');
            }
        }

        $oldClassId = $currentClass?->id;

        $enrollment->update([
            'class_id' => $newClass->id,
        ]);

        $enrollment->refresh()->loadMissing(['user', 'courseClass.course']);
        $this->lifecycleBlockchain->classChanged($enrollment, $currentClass, $newClass, $this->lifecycleContext($request), [
            'source' => 'student_portal',
            'trigger' => 'change_class',
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

        return back()->with('success', 'Đã chuyển sang đợt học ' . $newClass->name . '.');
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
                'error' => 'Số dư ví hiện chưa đủ. Vui lòng nạp tiền vào ví để mua khóa học.',
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
                'error' => 'Không thể trừ tiền từ ví vào lúc này. Vui lòng thử lại sau.',
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

$payment = Payment::create([
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
            'payment' => $payment,
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

    private function recordPromotionOnlyPayment(Course $course, CourseClass $class, array $pricing, bool $requiresManualApproval): Payment
    {
        return Payment::create([
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
                ? 'Miễn phí sau khi áp dụng khuyến mãi, chờ admin duyệt đăng ký.'
                : 'Miễn phí sau khi áp dụng khuyến mãi.',
            'metadata' => $this->promotionService->buildSnapshot($pricing),
        ]);
    }


    private function lifecycleContext(Request $request): array
    {
        return [
            'user_id' => Auth::id(),
            'username' => Auth::user()?->username,
            'role' => Auth::user()?->role,
            'ip' => $request->ip(),
        ];
    }

    private function syncEnrollmentCreationLifecycle(CourseEnrollment $enrollment, Request $request, array $extra = []): void
    {
        try {
            $context = $this->lifecycleContext($request);
            $this->lifecycleBlockchain->applicationCreated($enrollment, $context, $extra);
            $this->lifecycleBlockchain->classAssigned($enrollment, $enrollment->courseClass, $context, $extra);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function syncEnrollmentApprovedLifecycle(CourseEnrollment $enrollment, Request $request, array $extra = []): void
    {
        try {
            $this->lifecycleBlockchain->approved($enrollment, $this->lifecycleContext($request), $extra);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }

    private function syncPaymentLifecycle(Payment $payment, CourseEnrollment $enrollment, Request $request, array $extra = []): void
    {
        try {
            $this->lifecycleBlockchain->paymentRecorded($payment->fresh(['user', 'courseClass.course']), $enrollment, $this->lifecycleContext($request), $extra);
        } catch (\Throwable $exception) {
            report($exception);
        }
    }
}
