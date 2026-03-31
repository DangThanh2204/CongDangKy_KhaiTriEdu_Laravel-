<?php

namespace App\Http\Controllers;

use App\Models\WalletTransaction;
use App\Services\BlockchainAuditService;
use App\Services\FireflyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function __construct(
        protected FireflyService $firefly,
        protected BlockchainAuditService $blockchainAudit,
    ) {
    }

    public function index()
    {
        WalletTransaction::expireOverdueDirectTopups();

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();
        $transactions = $wallet->transactions()->latest()->paginate(10);

        $qrToken = session('qr_token');
        $qrAmount = session('qr_amount');
        $paymentMethod = session('payment_method');

        $directRequest = null;

        if ($paymentMethod === WalletTransaction::DIRECT_METHOD && $qrToken) {
            $directRequest = $wallet->transactions()
                ->where('reference', $qrToken)
                ->where('type', 'deposit')
                ->first();
        }

        if (! $directRequest || ! $directRequest->isDirectTopup()) {
            $directRequest = $wallet->transactions()
                ->where('type', 'deposit')
                ->where('metadata->method', WalletTransaction::DIRECT_METHOD)
                ->whereIn('status', ['pending', 'expired'])
                ->latest()
                ->first();
        }

        return view('wallet.index', compact(
            'wallet',
            'transactions',
            'qrToken',
            'qrAmount',
            'paymentMethod',
            'directRequest'
        ));
    }

    public function topUp(Request $request)
    {
        WalletTransaction::expireOverdueDirectTopups();

        $request->validate([
            'amount' => 'required|numeric|min:1000|max:10000000',
            'method' => 'required|in:direct,qr,bank',
        ]);

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();
        $amount = (float) $request->input('amount');
        $method = $request->input('method');

        $reference = $method === WalletTransaction::DIRECT_METHOD
            ? Str::upper('DEP' . uniqid())
            : Str::uuid()->toString();

        $expiresAt = $method === WalletTransaction::DIRECT_METHOD
            ? now()->addHours(WalletTransaction::DIRECT_TOPUP_EXPIRY_HOURS)
            : null;

        $transaction = $wallet->transactions()->create([
            'type' => 'deposit',
            'amount' => $amount,
            'status' => 'pending',
            'reference' => $reference,
            'expires_at' => $expiresAt,
            'metadata' => [
                'method' => $method,
                'requested_by' => $user->id,
            ],
        ]);

        \App\Services\SystemLogService::record('transaction', 'topup_requested', [
            'amount' => $amount,
            'method' => $method,
            'tx_id' => $transaction->id,
        ], $reference, $request);

        $auditResponse = $this->blockchainAudit->record('wallet.topup_requested', [
            'wallet_transaction_id' => $transaction->id,
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'method' => $method,
            'reference' => $reference,
            'firefly_identity' => $wallet->firefly_identity,
            'expires_at' => $expiresAt?->toDateTimeString(),
        ], [
            'reference' => $reference,
            'user_id' => $user->id,
            'username' => $user->username,
            'role' => $user->role,
            'ip' => $request->ip(),
        ]);
        $this->appendMetadata($transaction, ['blockchain_audit' => $auditResponse]);

        session()->flash('qr_token', $reference);
        session()->flash('qr_amount', $amount);
        session()->flash('payment_method', $method);

        if ($method === WalletTransaction::DIRECT_METHOD) {
            return redirect()->route('wallet.index')->with(
                'success',
                'Yêu cầu nạp tiền đã được tạo. Vui lòng mang mã thanh toán đến trung tâm trước ' . $transaction->expires_at_label . '.'
            );
        }

        return redirect()->route('wallet.index')->with(
            'success',
            'Giao dịch đã được tạo. Vui lòng thực hiện chuyển khoản và xác nhận khi hoàn tất.'
        );
    }

    public function confirmQr(Request $request)
    {
        WalletTransaction::expireOverdueDirectTopups();

        $request->validate([
            'token' => 'required|string',
        ]);

        $token = $request->input('token');
        $transaction = Auth::user()->getOrCreateWallet()->transactions()
            ->where('reference', $token)
            ->where('type', 'deposit')
            ->first();

        if (! $transaction) {
            return back()->with('error', 'Không tìm thấy giao dịch nạp tiền hợp lệ.');
        }

        if ($transaction->status === 'expired' || $transaction->isExpired()) {
            if ($transaction->status === 'pending') {
                $transaction->expire([
                    'expired_reason' => 'timeout',
                    'expired_by_system' => true,
                    'expired_at' => now()->toDateTimeString(),
                ]);
            }

            return back()->with('error', 'Yêu cầu thanh toán này đã hết hạn. Vui lòng tạo yêu cầu mới.');
        }

        if (! $transaction->isPending()) {
            return back()->with('error', 'Giao dịch này đã được xử lý trước đó.');
        }

        $method = data_get($transaction->metadata, 'method');
        if ($method === WalletTransaction::DIRECT_METHOD) {
            return back()->with('error', 'Giao dịch nạp trực tiếp phải được xác nhận bởi quản trị viên hoặc nhân viên. Vui lòng mang mã thanh toán tới điểm thu để được xử lý.');
        }

        if (! $transaction->complete()) {
            return back()->with('error', 'Không thể hoàn tất giao dịch.');
        }

        $fireflyResponse = $this->firefly->mint($transaction->wallet->firefly_identity, (float) $transaction->amount, [
            'reference' => $transaction->reference,
            'data' => [
                'type' => 'wallet_topup',
                'wallet_transaction_id' => $transaction->id,
                'wallet_id' => $transaction->wallet_id,
                'method' => $method,
                'amount' => (float) $transaction->amount,
                'reference' => $transaction->reference,
            ],
        ]);

        $auditResponse = $this->blockchainAudit->record('wallet.topup_confirmed', [
            'wallet_transaction_id' => $transaction->id,
            'wallet_id' => $transaction->wallet_id,
            'amount' => (float) $transaction->amount,
            'method' => $method,
            'reference' => $transaction->reference,
            'firefly_identity' => $transaction->wallet->firefly_identity,
            'firefly_tx_id' => $fireflyResponse['tx_id'] ?? null,
            'firefly_message_id' => $fireflyResponse['message_id'] ?? null,
        ], [
            'reference' => $transaction->reference,
            'user_id' => Auth::id(),
            'username' => Auth::user()?->username,
            'role' => Auth::user()?->role,
            'ip' => $request->ip(),
        ]);

        $this->appendMetadata($transaction, [
            'firefly' => $fireflyResponse,
            'blockchain_audit' => $auditResponse,
        ]);

        return redirect()->route('wallet.index')->with('success', 'Xác nhận thanh toán thành công, số dư đã được cập nhật.');
    }

    public function syncBalance()
    {
        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();

        $response = $this->firefly->getBalance($wallet->firefly_identity);
        if (! $response['success']) {
            return redirect()->route('wallet.index')->with('error', 'Không thể đồng bộ số dư: ' . ($response['message'] ?? 'Lỗi không xác định'));
        }

        $balance = null;
        $data = $response['data'] ?? null;

        if (is_array($data)) {
            if (isset($data['balance'])) {
                $balance = $data['balance'];
            } elseif (isset($data[0]['balance'])) {
                $balance = $data[0]['balance'];
            } elseif (isset($data[0]['available'])) {
                $balance = $data[0]['available'];
            }
        } elseif (is_numeric($data)) {
            $balance = $data;
        }

        if ($balance === null) {
            return redirect()->route('wallet.index')->with('error', 'Không thể xác định số dư trả về từ FireFly.');
        }

        $wallet->balance = $balance;
        $wallet->save();

        return redirect()->route('wallet.index')->with('success', 'Đã đồng bộ số dư với FireFly.');
    }

    protected function appendMetadata($transaction, array $payload): void
    {
        $transaction->metadata = array_merge($transaction->metadata ?? [], $payload);
        $transaction->save();
    }
}