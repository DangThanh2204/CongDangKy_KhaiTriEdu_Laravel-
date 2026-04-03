<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Services\BlockchainAuditService;
use App\Services\FireflyService;
use App\Services\SystemLogService;
use App\Services\VnpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function __construct(
        protected FireflyService $firefly,
        protected BlockchainAuditService $blockchainAudit,
        protected VnpayService $vnpay,
    ) {
    }

    public function index()
    {
        WalletTransaction::expireOverdueDirectTopups();

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();
        $transactions = $wallet->transactions()->latest()->paginate(10);

        $supportsVnpayTopup = $this->vnpay->isConfigured();
        $bankTransferConfig = $this->bankTransferConfig();
        $supportsBankTransferTopup = $this->supportsBankTransferTopup($bankTransferConfig);

        $directRequest = $wallet->transactions()
            ->where('type', 'deposit')
            ->where('metadata->method', WalletTransaction::DIRECT_METHOD)
            ->whereIn('status', ['pending', 'expired'])
            ->latest()
            ->first();

        $bankRequest = $wallet->transactions()
            ->where('type', 'deposit')
            ->where('metadata->method', WalletTransaction::BANK_METHOD)
            ->whereIn('status', ['pending', 'failed'])
            ->latest()
            ->first();

        $bankRequestQrUrl = $bankRequest ? $this->buildBankTransferQrUrl($bankRequest) : null;

        return view('wallet.index', compact(
            'wallet',
            'transactions',
            'directRequest',
            'bankRequest',
            'bankRequestQrUrl',
            'bankTransferConfig',
            'supportsBankTransferTopup',
            'supportsVnpayTopup'
        ));
    }

    public function topUp(Request $request)
    {
        WalletTransaction::expireOverdueDirectTopups();

        $request->validate([
            'amount' => 'required|numeric|min:1000|max:10000000',
            'method' => 'required|in:direct,bank,vnpay',
        ]);

        $user = Auth::user();
        $wallet = $user->getOrCreateWallet();
        $amount = (float) $request->input('amount');
        $method = (string) $request->input('method');

        $bankTransferConfig = $this->bankTransferConfig();

        if ($method === WalletTransaction::BANK_METHOD && ! $this->supportsBankTransferTopup($bankTransferConfig)) {
            return redirect()
                ->route('wallet.index')
                ->withInput()
                ->with('error', 'Hệ thống chưa cấu hình đầy đủ thông tin tài khoản ngân hàng để nhận chuyển khoản.');
        }

        $reference = match ($method) {
            WalletTransaction::DIRECT_METHOD => Str::upper('DEP' . uniqid()),
            WalletTransaction::BANK_METHOD => Str::upper('BNK' . now()->format('ymdHis') . random_int(100, 999)),
            WalletTransaction::VNPAY_METHOD => 'WVN' . Str::upper(Str::random(18)),
            default => Str::uuid()->toString(),
        };

        $expiresAt = $method === WalletTransaction::DIRECT_METHOD
            ? now()->addHours(WalletTransaction::DIRECT_TOPUP_EXPIRY_HOURS)
            : null;

        $metadata = [
            'method' => $method,
            'requested_by' => $user->id,
        ];

        if ($method === WalletTransaction::BANK_METHOD) {
            $metadata['bank_transfer'] = [
                'bank_name' => $bankTransferConfig['bank_name'],
                'bank_bin' => $bankTransferConfig['bank_bin'],
                'account_name' => $bankTransferConfig['account_name'],
                'account_number' => $bankTransferConfig['account_number'],
            ];
            $metadata['transfer_content'] = $reference;
        }

        $transaction = $wallet->transactions()->create([
            'type' => 'deposit',
            'amount' => $amount,
            'status' => 'pending',
            'reference' => $reference,
            'expires_at' => $expiresAt,
            'metadata' => $metadata,
        ]);

        SystemLogService::record('transaction', 'topup_requested', [
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

        if ($method === WalletTransaction::VNPAY_METHOD) {
            return redirect()
                ->route('wallet.vnpay.redirect', $transaction)
                ->with('success', 'Yêu cầu nạp tiền qua VNPay đã được tạo. Hệ thống sẽ chuyển bạn sang cổng thanh toán.');
        }

        if ($method === WalletTransaction::DIRECT_METHOD) {
            return redirect()
                ->route('wallet.index')
                ->with('success', 'Đã tạo mã nạp trực tiếp. Vui lòng mang mã này tới quầy và chờ admin xác nhận.');
        }

        return redirect()
            ->route('wallet.index')
            ->with('success', 'Đã tạo yêu cầu chuyển khoản. Hãy chuyển đúng số tiền, đúng nội dung rồi nhấn xác nhận để admin đối soát.');
    }

    public function redirectToVnpay(Request $request, WalletTransaction $walletTransaction)
    {
        abort_unless((int) $walletTransaction->wallet?->user_id === (int) Auth::id(), 403);

        WalletTransaction::expireOverdueDirectTopups();
        $walletTransaction->refresh();

        if (! $walletTransaction->isDeposit() || data_get($walletTransaction->metadata, 'method') !== WalletTransaction::VNPAY_METHOD) {
            return redirect()->route('wallet.index')->with('error', 'Giao dịch nạp ví này không sử dụng VNPay.');
        }

        if ((float) $walletTransaction->amount <= 0) {
            return redirect()->route('wallet.index')->with('error', 'Số tiền nạp ví phải lớn hơn 0 để gửi sang VNPay.');
        }

        if (! $walletTransaction->isPending()) {
            return redirect()->route('wallet.index')->with(
                $walletTransaction->status === 'completed' ? 'success' : 'error',
                $walletTransaction->status === 'completed'
                    ? 'Giao dịch nạp ví này đã được thanh toán thành công.'
                    : 'Giao dịch nạp ví này không còn ở trạng thái chờ xử lý.'
            );
        }

        $issues = $this->vnpay->configurationIssues();
        if ($issues !== []) {
            return redirect()->route('wallet.index')->with('error', 'VNPay chưa được cấu hình đầy đủ: ' . implode(' ', $issues));
        }

        try {
            $paymentUrl = $this->vnpay->buildWalletTopupUrl($walletTransaction, $request);
        } catch (\Throwable $exception) {
            SystemLogService::record('transaction', 'wallet_vnpay_redirect_exception', [
                'wallet_transaction_id' => $walletTransaction->id,
                'message' => $exception->getMessage(),
            ], $walletTransaction->reference, $request);

            return redirect()->route('wallet.index')->with('error', 'Không thể khởi tạo giao dịch VNPay cho ví: ' . $exception->getMessage());
        }

        $request->session()->put('browser_session_guard_skip_once', true);

        return redirect()->away($paymentUrl);
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
            if ($transaction->status === 'pending' && $transaction->isDirectTopup()) {
                $transaction->expire([
                    'expired_reason' => 'timeout',
                    'expired_by_system' => true,
                    'expired_at' => now()->toDateTimeString(),
                ]);
            }

            return back()->with('error', 'Yêu cầu thanh toán này đã hết hạn. Vui lòng tạo yêu cầu mới.');
        }

        $method = data_get($transaction->metadata, 'method');

        if ($method === WalletTransaction::DIRECT_METHOD) {
            return back()->with('error', 'Yêu cầu nạp trực tiếp chỉ được xác nhận bởi quản trị viên hoặc nhân viên tại quầy.');
        }

        if ($method === WalletTransaction::VNPAY_METHOD) {
            return back()->with('error', 'Giao dịch VNPay sẽ được hệ thống xác nhận tự động sau khi thanh toán thành công.');
        }

        if ($method === WalletTransaction::BANK_METHOD) {
            if ($transaction->status !== 'pending') {
                return back()->with('error', 'Yêu cầu chuyển khoản này đã được xử lý trước đó.');
            }

            $metadata = array_merge($transaction->metadata ?? [], [
                'user_transfer_confirmed_at' => now()->toDateTimeString(),
                'user_transfer_confirmed_ip' => $request->ip(),
            ]);

            $transaction->metadata = $metadata;
            $transaction->save();

            SystemLogService::record('transaction', 'bank_topup_marked_transferred', [
                'wallet_tx_id' => $transaction->id,
                'amount' => $transaction->amount,
                'reference' => $transaction->reference,
            ], $transaction->reference, $request);

            return redirect()
                ->route('wallet.index')
                ->with('success', 'Đã ghi nhận bạn đã chuyển khoản. Admin sẽ kiểm tra và cộng tiền vào ví sau khi đối soát.');
        }

        if (! $transaction->isPending()) {
            return back()->with('error', 'Giao dịch này đã được xử lý trước đó.');
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

    protected function appendMetadata(WalletTransaction $transaction, array $payload): void
    {
        $transaction->metadata = array_merge($transaction->metadata ?? [], $payload);
        $transaction->save();
    }

    protected function bankTransferConfig(): array
    {
        return [
            'bank_name' => trim((string) Setting::get('wallet_bank_name', '')),
            'bank_bin' => trim((string) Setting::get('wallet_bank_bin', '')),
            'account_name' => trim((string) Setting::get('wallet_bank_account_name', '')),
            'account_number' => trim((string) Setting::get('wallet_bank_account_number', '')),
        ];
    }

    protected function supportsBankTransferTopup(array $config): bool
    {
        return $config['bank_name'] !== ''
            && $config['account_name'] !== ''
            && $config['account_number'] !== '';
    }

    protected function buildBankTransferQrUrl(WalletTransaction $transaction): ?string
    {
        $snapshot = data_get($transaction->metadata, 'bank_transfer', []);
        $bankBin = trim((string) data_get($snapshot, 'bank_bin'));
        $accountNumber = trim((string) data_get($snapshot, 'account_number'));
        $accountName = trim((string) data_get($snapshot, 'account_name'));
        $transferContent = trim((string) data_get($transaction->metadata, 'transfer_content', $transaction->reference));

        if ($bankBin === '' || $accountNumber === '' || $accountName === '' || $transferContent === '') {
            return null;
        }

        return 'https://img.vietqr.io/image/'
            . rawurlencode($bankBin)
            . '-'
            . rawurlencode($accountNumber)
            . '-compact2.png?'
            . http_build_query([
                'amount' => (int) round((float) $transaction->amount),
                'addInfo' => $transferContent,
                'accountName' => $accountName,
            ]);
    }
}
