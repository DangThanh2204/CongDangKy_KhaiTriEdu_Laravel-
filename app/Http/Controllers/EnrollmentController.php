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
use App\Services\FireflyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class EnrollmentController extends Controller
{
    public function __construct(
        protected FireflyService $firefly,
        protected BlockchainAuditService $blockchainAudit,
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

        if ($course->isOffline() && $class->is_full) {
            return back()->withInput()->with('error', 'Đợt học này đã đủ số lượng học viên.');
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
                    ? 'Bạn đã có yêu cầu đăng ký chờ duyệt ở đợt học ' . $currentClassName . '.'
                    : 'Bạn đã có yêu cầu đăng ký khóa học này đang chờ duyệt.');
            }

            if ($existing->isApproved() || $existing->isCompleted()) {
                return back()->with('error', $currentClassName
                    ? 'Bạn đã đăng ký khóa học này ở đợt học ' . $currentClassName . ' rồi.'
                    : 'Bạn đã đăng ký khóa học này rồi.');
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

                return back()->with('success', 'Đăng ký thành công, bạn có thể học ngay.');
            }
        }

        $finalPrice = (float) $course->final_price;
        $requiresManualApproval = $course->requiresManualApproval();

        if ($finalPrice > 0) {
            $requestedMethod = (string) $request->input('payment_method', 'wallet');

            if ($requestedMethod !== 'wallet') {
                return back()
                    ->withInput()
                    ->with('error', 'Khóa học trả phí hiện chỉ hỗ trợ thanh toán bằng số dư ví. Vui lòng nạp tiền vào ví rồi đăng ký lại.');
            }

            $wallet = Auth::user()->getOrCreateWallet();

            if ((float) $wallet->balance < $finalPrice) {
                return back()
                    ->with('error', 'Số dư ví hiện chưa đủ. Vui lòng nạp tiền vào ví để mua khóa học.')
                    ->with('required_topup', true);
            }

            $transaction = $wallet->charge($finalPrice, [
                'course_id' => $course->id,
                'class_id' => $class->id,
            ]);

            if (! $transaction) {
                return back()->with('error', 'Không thể trừ tiền từ ví vào lúc này. Vui lòng thử lại sau.');
            }

            $purchaseReference = $transaction->reference ?: ('COURSE-' . $course->id . '-' . Auth::id() . '-' . now()->format('YmdHis'));
            $fireflyResult = null;
            $auditResponse = null;

            try {
                $platformIdentity = $this->firefly->getPlatformIdentity();
                $fireflyResult = $this->firefly->transfer(
                    $wallet->firefly_identity,
                    $platformIdentity,
                    $finalPrice,
                    [
                        'reference' => $purchaseReference,
                        'data' => [
                            'type' => 'course_purchase',
                            'wallet_transaction_id' => $transaction->id,
                            'course_id' => $course->id,
                            'class_id' => $class->id,
                            'user_id' => Auth::id(),
                            'amount' => $finalPrice,
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
                    'amount' => $finalPrice,
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
                'amount' => $finalPrice,
                'method' => 'wallet',
                'status' => 'completed',
                'paid_at' => now(),
                'reference' => $purchaseReference,
                'notes' => $requiresManualApproval
                    ? 'Thanh toán bằng ví nội bộ, chờ admin duyệt đăng ký.'
                    : 'Thanh toán bằng ví nội bộ.',
            ]);

            $enrollment = $this->storeEnrollmentRequest(Auth::id(), $class);

            if ($requiresManualApproval) {
                return back()->with('success', 'Thanh toán bằng ví thành công. Yêu cầu đăng ký của bạn đang chờ admin duyệt.');
            }

            $enrollment->approve();

            return back()->with('success', 'Thanh toán bằng ví thành công, bạn có thể học ngay.');
        }

        $enrollment = $this->storeEnrollmentRequest(Auth::id(), $class);

        if ($requiresManualApproval) {
            return back()->with('success', 'Đăng ký thành công, vui lòng chờ admin duyệt.');
        }

        $enrollment->approve();

        return back()->with('success', 'Đăng ký thành công, bạn có thể học ngay.');
    }

    public function unenroll(Course $course)
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

        if (Schema::hasTable('class_change_logs')) {
            ClassChangeLog::create([
                'enrollment_id' => $enrollment->id,
                'user_id' => Auth::id(),
                'old_class_id' => $oldClassId,
                'new_class_id' => $newClass->id,
                'reason' => 'student_self_service',
            ]);
        }

        return back()->with('success', 'Đã chuyển sang đợt học ' . $newClass->name . '.');
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
}
