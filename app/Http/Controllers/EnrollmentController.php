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
            return back()->with('error', 'Khóa học không khả dụng.');
        }

        $class = $this->resolveSelectedClass($request, $course);

        if (! $class) {
            $message = $course->isOffline()
                ? 'Vui lòng chọn một đợt học hợp lệ đang mở đăng ký.'
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
            $method = $request->input('payment_method', 'wallet');

            if ($method === 'wallet') {
                $wallet = Auth::user()->getOrCreateWallet();

                if ($wallet->balance < $finalPrice) {
                    return back()
                        ->with('error', 'Số dư không đủ. Vui lòng nạp tiền vào ví để mua khóa học.')
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
                    return back()->with('success', 'Đăng ký thành công, vui lòng chờ admin duyệt.');
                }

                $enrollment->approve();

                return back()->with('success', 'Đăng ký thành công, bạn có thể học ngay.');
            }

            if ($method === 'vnpay') {
                if (! $this->vnpay->isConfigured()) {
                    return back()
                        ->withInput()
                        ->with('error', 'VNPay chưa được cấu hình. Vui lòng liên hệ quản trị viên.');
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
                        'notes' => 'Khởi tạo thanh toán VNPay',
                    ]);
                }

                return redirect()
                    ->route('payments.vnpay.redirect', $payment)
                    ->with('success', 'Đang chuyển sang cổng thanh toán VNPay.');
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
                        ? 'Đăng ký thành công, vui lòng thanh toán và chờ admin duyệt.'
                        : 'Phiếu thanh toán đã được tạo. Sau khi thanh toán thành công, bạn sẽ được xử lý đăng ký.');
            }
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

        if ($course->learning_type === 'online') {
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

        if (preg_match('/chủ\s*nhật|chu\s*nhat|\bcn\b/u', $normalized)) {
            $days[] = 'CN';
        }

        return array_values(array_unique($days));
    }
}
