<?php

namespace App\Http\Controllers;

use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\WalletTransaction;
use App\Services\BlockchainAuditService;
use App\Services\FireflyService;
use App\Services\SystemLogService;
use App\Services\VnpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function __construct(
        protected VnpayService $vnpay,
        protected FireflyService $firefly,
        protected BlockchainAuditService $blockchainAudit,
    ) {
    }

    public function show(Payment $payment)
    {
        if (Auth::id() !== $payment->user_id && ! optional(auth()->user())->isAdmin()) {
            abort(403);
        }

        $payment->loadMissing('courseClass.course');
        $vnpaySummary = $payment->isVnpay() ? $this->vnpay->configurationSummary() : null;

        return view('payments.slip', compact('payment', 'vnpaySummary'));
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

        $issues = $this->vnpay->configurationIssues();
        if ($issues !== []) {
            return redirect()
                ->route('payments.show', $payment)
                ->with('error', 'VNPay chưa được cấu hình đầy đủ: ' . implode(' ', $issues));
        }

        if ((float) $payment->amount <= 0) {
            return redirect()
                ->route('payments.show', $payment)
                ->with('error', 'Số tiền thanh toán phải lớn hơn 0 để gửi sang VNPay.');
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

        try {
            $paymentUrl = $this->vnpay->buildPaymentUrl($payment, $request);
        } catch (\Throwable $exception) {
            SystemLogService::record(
                'transaction',
                'payment_vnpay_redirect_exception',
                [
                    'payment_id' => $payment->id,
                    'message' => $exception->getMessage(),
                ],
                $payment->reference,
                $request
            );

            return redirect()
                ->route('payments.show', $payment)
                ->with('error', 'Không thể khởi tạo giao dịch VNPay: ' . $exception->getMessage());
        }

        $this->markBrowserSessionGuardBypass($request);

        return redirect()->away($paymentUrl);
    }

    public function vnpayReturn(Request $request)
    {
        $this->markBrowserSessionGuardBypass($request);

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

        if ($payment) {
            return $this->handlePaymentReturn($payment, $payload, $request);
        }

        $walletTransaction = $this->findWalletTopupByReference($reference);
        if ($walletTransaction) {
            return $this->handleWalletTopupReturn($walletTransaction, $payload, $request);
        }

        SystemLogService::record(
            'transaction',
            'payment_vnpay_not_found',
            ['payload' => $payload],
            $reference,
            $request
        );

        return redirect()
            ->route('home')
            ->with('error', 'Không tìm thấy giao dịch VNPay tương ứng.');
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

        if ($payment) {
            return $this->handlePaymentIpn($payment, $payload, $request);
        }

        $walletTransaction = $this->findWalletTopupByReference($reference);
        if ($walletTransaction) {
            return $this->handleWalletTopupIpn($walletTransaction, $payload, $request);
        }

        return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
    }

    private function handlePaymentReturn(Payment $payment, array $payload, Request $request)
    {
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

                return $this->redirectAfterGateway(
                    $payment->fresh(['courseClass.course']),
                    'success',
                    $this->successMessageForPayment($payment)
                );
            }

            $this->processFailedPayment($payment, $payload, $request);

            return $this->redirectAfterGateway(
                $payment->fresh(['courseClass.course']),
                'error',
                'Thanh toán VNPay chưa hoàn tất: ' . $this->vnpay->responseMessage($payload)
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

    private function handleWalletTopupReturn(WalletTransaction $walletTransaction, array $payload, Request $request)
    {
        if (! $this->vnpay->amountMatchesValue((float) $walletTransaction->amount, $payload)) {
            SystemLogService::record(
                'transaction',
                'wallet_vnpay_amount_mismatch',
                [
                    'wallet_transaction_id' => $walletTransaction->id,
                    'expected_amount' => $walletTransaction->amount,
                    'payload' => $payload,
                ],
                $walletTransaction->reference,
                $request
            );

            return $this->redirectAfterWalletGateway('error', 'Số tiền nạp ví từ VNPay không khớp với giao dịch.');
        }

        if ($walletTransaction->status === 'completed') {
            return $this->redirectAfterWalletGateway('success', $this->successMessageForWalletTopup());
        }

        if (in_array($walletTransaction->status, ['failed', 'expired'], true)) {
            return $this->redirectAfterWalletGateway('error', 'Giao dịch nạp ví này không còn ở trạng thái chờ xử lý.');
        }

        try {
            if ($this->vnpay->isSuccessful($payload)) {
                $this->processSuccessfulWalletTopup($walletTransaction, $payload, $request);

                return $this->redirectAfterWalletGateway('success', $this->successMessageForWalletTopup());
            }

            $this->processFailedWalletTopup($walletTransaction, $payload, $request);

            return $this->redirectAfterWalletGateway(
                'error',
                'Nạp tiền qua VNPay chưa hoàn tất: ' . $this->vnpay->responseMessage($payload)
            );
        } catch (\Throwable $exception) {
            SystemLogService::record(
                'transaction',
                'wallet_vnpay_return_exception',
                [
                    'wallet_transaction_id' => $walletTransaction->id,
                    'message' => $exception->getMessage(),
                ],
                $walletTransaction->reference,
                $request
            );

            return $this->redirectAfterWalletGateway(
                'error',
                'Có lỗi xảy ra khi cập nhật kết quả nạp ví qua VNPay. Vui lòng liên hệ quản trị viên.'
            );
        }
    }

    private function handlePaymentIpn(Payment $payment, array $payload, Request $request)
    {
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

    private function handleWalletTopupIpn(WalletTransaction $walletTransaction, array $payload, Request $request)
    {
        if (! $this->vnpay->amountMatchesValue((float) $walletTransaction->amount, $payload)) {
            SystemLogService::record(
                'transaction',
                'wallet_vnpay_amount_mismatch',
                [
                    'wallet_transaction_id' => $walletTransaction->id,
                    'expected_amount' => $walletTransaction->amount,
                    'payload' => $payload,
                ],
                $walletTransaction->reference,
                $request
            );

            return response()->json(['RspCode' => '04', 'Message' => 'Invalid amount']);
        }

        if (in_array($walletTransaction->status, ['completed', 'failed', 'expired'], true)) {
            return response()->json(['RspCode' => '02', 'Message' => 'Order already confirmed']);
        }

        try {
            if ($this->vnpay->isSuccessful($payload)) {
                $this->processSuccessfulWalletTopup($walletTransaction, $payload, $request);
            } else {
                $this->processFailedWalletTopup($walletTransaction, $payload, $request);
            }

            return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
        } catch (\Throwable $exception) {
            SystemLogService::record(
                'transaction',
                'wallet_vnpay_ipn_exception',
                [
                    'wallet_transaction_id' => $walletTransaction->id,
                    'message' => $exception->getMessage(),
                ],
                $walletTransaction->reference,
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

            $payment->markFailed(
                trim($this->vnpay->gatewaySummary($payload) . ' | ' . $this->vnpay->responseMessage($payload), ' |')
            );
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

    private function processSuccessfulWalletTopup(WalletTransaction $walletTransaction, array $payload, Request $request): void
    {
        $completed = DB::transaction(function () use ($walletTransaction, $payload) {
            $walletTransaction->refresh();

            if (! $walletTransaction->isPending()) {
                return false;
            }

            $walletTransaction->metadata = array_merge($walletTransaction->metadata ?? [], [
                'vnpay' => [
                    'gateway_summary' => $this->vnpay->gatewaySummary($payload),
                    'response_message' => $this->vnpay->responseMessage($payload),
                    'payload' => $payload,
                    'confirmed_at' => now()->toDateTimeString(),
                ],
            ]);
            $walletTransaction->save();

            return $walletTransaction->complete();
        });

        if (! $completed) {
            return;
        }

        $walletTransaction->refresh()->loadMissing('wallet.user');

        $fireflyResponse = $this->firefly->mint($walletTransaction->wallet->firefly_identity, (float) $walletTransaction->amount, [
            'reference' => $walletTransaction->reference,
            'data' => [
                'type' => 'wallet_topup',
                'wallet_transaction_id' => $walletTransaction->id,
                'wallet_id' => $walletTransaction->wallet_id,
                'method' => data_get($walletTransaction->metadata, 'method'),
                'amount' => (float) $walletTransaction->amount,
                'reference' => $walletTransaction->reference,
                'gateway' => 'vnpay',
            ],
        ]);

        $auditResponse = $this->blockchainAudit->record('wallet.topup_confirmed_via_vnpay', [
            'wallet_transaction_id' => $walletTransaction->id,
            'wallet_id' => $walletTransaction->wallet_id,
            'amount' => (float) $walletTransaction->amount,
            'method' => data_get($walletTransaction->metadata, 'method'),
            'reference' => $walletTransaction->reference,
            'gateway_summary' => $this->vnpay->gatewaySummary($payload),
            'firefly_identity' => $walletTransaction->wallet->firefly_identity,
            'firefly_tx_id' => $fireflyResponse['tx_id'] ?? null,
            'firefly_message_id' => $fireflyResponse['message_id'] ?? null,
        ], [
            'reference' => $walletTransaction->reference,
            'user_id' => $walletTransaction->wallet->user_id,
            'username' => $walletTransaction->wallet->user->username ?? null,
            'role' => $walletTransaction->wallet->user->role ?? null,
            'ip' => $request->ip(),
        ]);

        $this->appendWalletMetadata($walletTransaction, [
            'firefly' => $fireflyResponse,
            'blockchain_audit' => $auditResponse,
        ]);

        SystemLogService::record(
            'transaction',
            'wallet_vnpay_completed',
            [
                'wallet_transaction_id' => $walletTransaction->id,
                'amount' => $walletTransaction->amount,
                'payload' => $payload,
            ],
            $walletTransaction->reference,
            $request
        );
    }

    private function processFailedWalletTopup(WalletTransaction $walletTransaction, array $payload, Request $request): void
    {
        DB::transaction(function () use ($walletTransaction, $payload) {
            $walletTransaction->refresh();

            if (! $walletTransaction->isPending()) {
                return;
            }

            $walletTransaction->fail([
                'vnpay' => [
                    'gateway_summary' => $this->vnpay->gatewaySummary($payload),
                    'response_message' => $this->vnpay->responseMessage($payload),
                    'payload' => $payload,
                    'failed_at' => now()->toDateTimeString(),
                ],
                'failed_reason' => trim($this->vnpay->gatewaySummary($payload) . ' | ' . $this->vnpay->responseMessage($payload), ' |'),
            ]);
        });

        SystemLogService::record(
            'transaction',
            'wallet_vnpay_failed',
            [
                'wallet_transaction_id' => $walletTransaction->id,
                'amount' => $walletTransaction->amount,
                'payload' => $payload,
            ],
            $walletTransaction->reference,
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

    private function successMessageForWalletTopup(): string
    {
        return 'Nạp tiền qua VNPay thành công, số dư ví đã được cập nhật.';
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

    private function redirectAfterWalletGateway(string $flashType, string $message)
    {
        return redirect()->route('wallet.index')->with($flashType, $message);
    }

    private function findWalletTopupByReference(?string $reference): ?WalletTransaction
    {
        if (! is_string($reference) || trim($reference) === '') {
            return null;
        }

        return WalletTransaction::query()
            ->with('wallet.user')
            ->where('reference', $reference)
            ->where('type', 'deposit')
            ->where('metadata->method', 'vnpay')
            ->first();
    }

    private function appendWalletMetadata(WalletTransaction $walletTransaction, array $payload): void
    {
        $walletTransaction->metadata = array_merge($walletTransaction->metadata ?? [], $payload);
        $walletTransaction->save();
    }
    private function markBrowserSessionGuardBypass(Request $request): void
    {
        $request->session()->put('browser_session_guard_skip_once', true);
    }
}