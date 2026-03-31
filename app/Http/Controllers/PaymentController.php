<?php

namespace App\Http\Controllers;

use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Services\SystemLogService;
use App\Services\VnpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(protected VnpayService $vnpay)
    {
    }

    public function show(Payment $payment)
    {
        if (Auth::id() !== $payment->user_id && ! optional(auth()->user())->isAdmin()) {
            abort(403);
        }

        $payment->loadMissing('courseClass.course');

        return view('payments.slip', compact('payment'));
    }

    public function redirectToVnpay(Request $request, Payment $payment)
    {
        if (Auth::id() !== $payment->user_id && ! optional(auth()->user())->isAdmin()) {
            abort(403);
        }

        if (! $payment->isVnpay()) {
            return redirect()
                ->route('payments.show', $payment)
                ->with('error', 'Phiếu thanh toán này không sử dụng VNPay.');
        }

        if (! $this->vnpay->isConfigured()) {
            return redirect()
                ->route('payments.show', $payment)
                ->with('error', 'VNPay chưa được cấu hình. Vui lòng liên hệ quản trị viên.');
        }

        if (! $payment->isPending()) {
            return redirect()
                ->route('payments.show', $payment)
                ->with(
                    $payment->isCompleted() ? 'success' : 'error',
                    $payment->isCompleted()
                        ? 'Phiếu thanh toán này đã được xử lý thành công.'
                        : 'Phiếu thanh toán này không còn ở trạng thái chờ xử lý.'
                );
        }

        $payment->loadMissing('courseClass.course');

        return redirect()->away($this->vnpay->buildPaymentUrl($payment, $request));
    }

    public function vnpayReturn(Request $request)
    {
        $verification = $this->vnpay->verifyResponse($request->query());

        if (! $verification['is_valid']) {
            SystemLogService::record(
                'transaction',
                'payment_vnpay_invalid_checksum',
                ['query' => $request->query()],
                null,
                $request
            );

            return redirect()
                ->route('home')
                ->with('error', 'Phản hồi từ VNPay không hợp lệ.');
        }

        $payload = $verification['payload'];
        $reference = $this->vnpay->paymentReference($payload);
        $payment = $reference
            ? Payment::with(['courseClass.course'])->where('reference', $reference)->first()
            : null;

        if (! $payment) {
            SystemLogService::record(
                'transaction',
                'payment_vnpay_not_found',
                ['payload' => $payload],
                $reference,
                $request
            );

            return redirect()
                ->route('home')
                ->with('error', 'Không tìm thấy phiếu thanh toán VNPay tương ứng.');
        }

        if (! $this->vnpay->amountMatches($payment, $payload)) {
            SystemLogService::record(
                'transaction',
                'payment_vnpay_amount_mismatch',
                [
                    'payment_id' => $payment->id,
                    'expected_amount' => $payment->amount,
                    'payload' => $payload,
                ],
                $payment->reference,
                $request
            );

            return $this->redirectAfterGateway(
                $payment,
                'error',
                'Số tiền thanh toán VNPay không khớp với đơn hàng.'
            );
        }

        if ($payment->isCompleted()) {
            return $this->redirectAfterGateway($payment, 'success', $this->successMessageForPayment($payment));
        }

        if ($payment->isFailed()) {
            return $this->redirectAfterGateway(
                $payment,
                'error',
                'Phiếu thanh toán này đã được ghi nhận là thất bại hoặc bị hủy trước đó.'
            );
        }

        try {
            if ($this->vnpay->isSuccessful($payload)) {
                $this->processSuccessfulPayment($payment, $payload, $request);

                return $this->redirectAfterGateway($payment->fresh(['courseClass.course']), 'success', $this->successMessageForPayment($payment));
            }

            $this->processFailedPayment($payment, $payload, $request);

            return $this->redirectAfterGateway(
                $payment->fresh(['courseClass.course']),
                'error',
                'Thanh toán VNPay chưa hoàn tất hoặc đã bị hủy.'
            );
        } catch (\Throwable $exception) {
            SystemLogService::record(
                'transaction',
                'payment_vnpay_return_exception',
                [
                    'payment_id' => $payment->id,
                    'message' => $exception->getMessage(),
                ],
                $payment->reference,
                $request
            );

            return $this->redirectAfterGateway(
                $payment,
                'error',
                'Có lỗi xảy ra khi cập nhật kết quả thanh toán VNPay. Vui lòng liên hệ quản trị viên.'
            );
        }
    }

    public function vnpayIpn(Request $request)
    {
        $verification = $this->vnpay->verifyResponse($request->query());

        if (! $verification['is_valid']) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid checksum']);
        }

        $payload = $verification['payload'];
        $reference = $this->vnpay->paymentReference($payload);
        $payment = $reference
            ? Payment::with(['courseClass.course'])->where('reference', $reference)->first()
            : null;

        if (! $payment) {
            return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
        }

        if (! $this->vnpay->amountMatches($payment, $payload)) {
            SystemLogService::record(
                'transaction',
                'payment_vnpay_amount_mismatch',
                [
                    'payment_id' => $payment->id,
                    'expected_amount' => $payment->amount,
                    'payload' => $payload,
                ],
                $payment->reference,
                $request
            );

            return response()->json(['RspCode' => '04', 'Message' => 'Invalid amount']);
        }

        if ($payment->isCompleted() || $payment->isFailed()) {
            return response()->json(['RspCode' => '02', 'Message' => 'Order already confirmed']);
        }

        try {
            if ($this->vnpay->isSuccessful($payload)) {
                $this->processSuccessfulPayment($payment, $payload, $request);
            } else {
                $this->processFailedPayment($payment, $payload, $request);
            }

            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
        } catch (\Throwable $exception) {
            SystemLogService::record(
                'transaction',
                'payment_vnpay_ipn_exception',
                [
                    'payment_id' => $payment->id,
                    'message' => $exception->getMessage(),
                ],
                $payment->reference,
                $request
            );

            return response()->json(['RspCode' => '99', 'Message' => 'Unknown error']);
        }
    }

    private function processSuccessfulPayment(Payment $payment, array $payload, Request $request): void
    {
        DB::transaction(function () use ($payment, $payload) {
            $payment->refresh();

            if (! $payment->isPending()) {
                return;
            }

            $payment->markCompleted($this->vnpay->gatewaySummary($payload));
            $this->ensureEnrollmentForPayment($payment);
        });

        SystemLogService::record(
            'transaction',
            'payment_vnpay_completed',
            [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'payload' => $payload,
            ],
            $payment->reference,
            $request
        );
    }

    private function processFailedPayment(Payment $payment, array $payload, Request $request): void
    {
        DB::transaction(function () use ($payment, $payload) {
            $payment->refresh();

            if (! $payment->isPending()) {
                return;
            }

            $payment->markFailed($this->vnpay->gatewaySummary($payload));
        });

        SystemLogService::record(
            'transaction',
            'payment_vnpay_failed',
            [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'payload' => $payload,
            ],
            $payment->reference,
            $request
        );
    }

    private function ensureEnrollmentForPayment(Payment $payment): CourseEnrollment
    {
        $payment->loadMissing('courseClass.course');
        $course = $payment->courseClass?->course;

        $enrollment = CourseEnrollment::query()
            ->where('user_id', $payment->user_id)
            ->where('class_id', $payment->class_id)
            ->latest('id')
            ->first();

        if (! $enrollment) {
            $enrollment = new CourseEnrollment([
                'user_id' => $payment->user_id,
                'class_id' => $payment->class_id,
            ]);
        }

        if (! $enrollment->exists || $enrollment->isRejected() || $enrollment->isCancelled()) {
            $enrollment->forceFill([
                'status' => 'pending',
                'enrolled_at' => $enrollment->enrolled_at ?: now(),
                'approved_at' => null,
                'rejected_at' => null,
                'cancelled_at' => null,
                'completed_at' => null,
                'notes' => 'Thanh toán qua VNPay: ' . $payment->reference,
            ])->save();
        } elseif (! $enrollment->enrolled_at) {
            $enrollment->forceFill([
                'enrolled_at' => now(),
            ])->save();
        }

        if ($course?->isOnline() && ! $enrollment->isApproved() && ! $enrollment->isCompleted()) {
            $enrollment->approve();
        }

        return $enrollment->fresh();
    }

    private function successMessageForPayment(Payment $payment): string
    {
        $payment->loadMissing('courseClass.course');

        return $payment->courseClass?->course?->isOffline()
            ? 'Thanh toán VNPay thành công, yêu cầu đăng ký đã được gửi và đang chờ admin duyệt.'
            : 'Thanh toán VNPay thành công, bạn có thể vào học ngay.';
    }

    private function redirectAfterGateway(Payment $payment, string $flashType, string $message)
    {
        $payment->loadMissing('courseClass.course');
        $course = $payment->courseClass?->course;

        if ($course) {
            return redirect()->route('courses.show', $course)->with($flashType, $message);
        }

        return redirect()->route('home')->with($flashType, $message);
    }
}