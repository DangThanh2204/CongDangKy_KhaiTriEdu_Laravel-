<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\WalletTransaction;
use App\Services\SystemLogService;
use App\Services\VnpayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WalletController extends Controller
{
    public function __construct(protected VnpayService $vnpay)
    {
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
                ->with('error', 'He thong chua cau hinh day du thong tin tai khoan ngan hang de nhan chuyen khoan.');
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

        if ($method === WalletTransaction::VNPAY_METHOD) {
            return redirect()
                ->route('wallet.vnpay.redirect', $transaction)
                ->with('success', 'Yeu cau nap tien qua VNPay da duoc tao. He thong se chuyen ban sang cong thanh toan.');
        }

        if ($method === WalletTransaction::DIRECT_METHOD) {
            return redirect()
                ->route('wallet.index')
                ->with('success', 'Da tao ma nap truc tiep. Vui long mang ma nay toi quay va cho admin xac nhan.');
        }

        return redirect()
            ->route('wallet.index')
            ->with('success', 'Da tao yeu cau chuyen khoan. Hay chuyen dung so tien, dung noi dung roi nhan xac nhan de admin doi soat.');
    }

    public function redirectToVnpay(Request $request, WalletTransaction $walletTransaction)
    {
        abort_unless((int) $walletTransaction->wallet?->user_id === (int) Auth::id(), 403);

        WalletTransaction::expireOverdueDirectTopups();
        $walletTransaction->refresh();

        if (! $walletTransaction->isDeposit() || data_get($walletTransaction->metadata, 'method') !== WalletTransaction::VNPAY_METHOD) {
            return redirect()->route('wallet.index')->with('error', 'Giao dich nap vi nay khong su dung VNPay.');
        }

        if ((float) $walletTransaction->amount <= 0) {
            return redirect()->route('wallet.index')->with('error', 'So tien nap vi phai lon hon 0 de gui sang VNPay.');
        }

        if (! $walletTransaction->isPending()) {
            return redirect()->route('wallet.index')->with(
                $walletTransaction->status === 'completed' ? 'success' : 'error',
                $walletTransaction->status === 'completed'
                    ? 'Giao dich nap vi nay da duoc thanh toan thanh cong.'
                    : 'Giao dich nap vi nay khong con o trang thai cho xu ly.'
            );
        }

        $issues = $this->vnpay->configurationIssues();
        if ($issues !== []) {
            return redirect()->route('wallet.index')->with('error', 'VNPay chua duoc cau hinh day du: ' . implode(' ', $issues));
        }

        try {
            $paymentUrl = $this->vnpay->buildWalletTopupUrl($walletTransaction, $request);
        } catch (\Throwable $exception) {
            SystemLogService::record('transaction', 'wallet_vnpay_redirect_exception', [
                'wallet_transaction_id' => $walletTransaction->id,
                'message' => $exception->getMessage(),
            ], $walletTransaction->reference, $request);

            return redirect()->route('wallet.index')->with('error', 'Khong the khoi tao giao dich VNPay cho vi: ' . $exception->getMessage());
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
            return back()->with('error', 'Khong tim thay giao dich nap tien hop le.');
        }

        if ($transaction->status === 'expired' || $transaction->isExpired()) {
            if ($transaction->status === 'pending' && $transaction->isDirectTopup()) {
                $transaction->expire([
                    'expired_reason' => 'timeout',
                    'expired_by_system' => true,
                    'expired_at' => now()->toDateTimeString(),
                ]);
            }

            return back()->with('error', 'Yeu cau thanh toan nay da het han. Vui long tao yeu cau moi.');
        }

        $method = data_get($transaction->metadata, 'method');

        if ($method === WalletTransaction::DIRECT_METHOD) {
            return back()->with('error', 'Yeu cau nap truc tiep chi duoc xac nhan boi quan tri vien hoac nhan vien tai quay.');
        }

        if ($method === WalletTransaction::VNPAY_METHOD) {
            return back()->with('error', 'Giao dich VNPay se duoc he thong xac nhan tu dong sau khi thanh toan thanh cong.');
        }

        if ($method === WalletTransaction::BANK_METHOD) {
            if ($transaction->status !== 'pending') {
                return back()->with('error', 'Yeu cau chuyen khoan nay da duoc xu ly truoc do.');
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
                ->with('success', 'Da ghi nhan ban da chuyen khoan. Admin se kiem tra va cong tien vao vi sau khi doi soat.');
        }

        if (! $transaction->isPending()) {
            return back()->with('error', 'Giao dich nay da duoc xu ly truoc do.');
        }

        if (! $transaction->complete()) {
            return back()->with('error', 'Khong the hoan tat giao dich.');
        }

        return redirect()->route('wallet.index')->with('success', 'Xac nhan thanh toan thanh cong, so du da duoc cap nhat.');
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
