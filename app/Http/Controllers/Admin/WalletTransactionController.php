<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Services\BlockchainAuditService;
use App\Services\CsvExportService;
use App\Services\FireflyService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletTransactionController extends Controller
{
    public function __construct(
        protected FireflyService $firefly,
        protected BlockchainAuditService $blockchainAudit,
    ) {
    }

    public function index(Request $request)
    {
        $this->syncExpiredDirectTopups();

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $transactions = $this->filteredQuery($request)
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'expired' THEN 1 ELSE 2 END")
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.wallet-transactions.index', compact('transactions', 'fromDate', 'toDate'));
    }

    public function export(Request $request, CsvExportService $csvExportService)
    {
        $this->syncExpiredDirectTopups();

        $transactions = $this->filteredQuery($request)
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 WHEN status = 'expired' THEN 1 ELSE 2 END")
            ->latest()
            ->get();

        return $csvExportService->download(
            'wallet-transactions-' . now()->format('Y-m-d-His') . '.csv',
            ['ID', 'Học viên', 'Email', 'Số tiền', 'Mã giao dịch', 'Phương thức', 'Trạng thái', 'Bắt đầu', 'Hết hạn', 'Hết hạn lúc'],
            $transactions->map(function (WalletTransaction $transaction) {
                return [
                    $transaction->id,
                    $transaction->wallet->user->fullname ?? $transaction->wallet->user->username ?? '',
                    $transaction->wallet->user->email ?? '',
                    $transaction->amount,
                    $transaction->reference,
                    data_get($transaction->metadata, 'method', ''),
                    $transaction->status_label,
                    $transaction->requested_at_label,
                    $transaction->expires_at_label,
                    $transaction->expired_at_label,
                ];
            })
        );
    }

    protected function filteredQuery(Request $request): Builder
    {
        $search = $request->get('search');
        $status = $request->get('status');
        $method = $request->get('method');
        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $query = WalletTransaction::with('wallet.user')->where('type', 'deposit');

        if ($status) {
            $query->where('status', $status);
        }

        if ($method) {
            $query->where('metadata->method', $method);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        if ($search) {
            $query->where(function (Builder $builder) use ($search) {
                $builder->where('reference', 'like', "%{$search}%")
                    ->orWhereHas('wallet.user', function (Builder $userQuery) use ($search) {
                        $userQuery->where('fullname', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%");
                    });
            });
        }

        return $query;
    }

    public function show(WalletTransaction $walletTransaction)
    {
        $this->syncExpiredDirectTopups();
        $walletTransaction->refresh()->load('wallet.user');

        return view('admin.wallet-transactions.show', compact('walletTransaction'));
    }

    public function confirm(Request $request, WalletTransaction $walletTransaction)
    {
        $this->syncExpiredDirectTopups();
        $walletTransaction->refresh();

        if ($walletTransaction->status === 'expired' || $walletTransaction->isExpired()) {
            return back()->with('error', 'Yêu cầu nạp trực tiếp này đã hết hạn, không thể xác nhận nữa.');
        }

        if (! $walletTransaction->isPending()) {
            return back()->with('error', 'Giao dịch này đã được xử lý.');
        }

        $data = $request->validate([
            'admin_note' => 'nullable|string|max:500',
        ]);

        $walletTransaction->metadata = array_merge($walletTransaction->metadata ?? [], [
            'confirmed_by' => Auth::id(),
            'confirmed_at' => now()->toDateTimeString(),
        ]);

        if (! empty($data['admin_note'])) {
            $walletTransaction->metadata['admin_note'] = $data['admin_note'];
        }

        $walletTransaction->save();
        $walletTransaction->complete();

        $fireflyResponse = $this->firefly->mint($walletTransaction->wallet->firefly_identity, (float) $walletTransaction->amount, [
            'reference' => $walletTransaction->reference,
            'data' => [
                'type' => 'wallet_topup',
                'wallet_transaction_id' => $walletTransaction->id,
                'wallet_id' => $walletTransaction->wallet_id,
                'amount' => (float) $walletTransaction->amount,
                'method' => data_get($walletTransaction->metadata, 'method'),
                'confirmed_by' => Auth::id(),
                'reference' => $walletTransaction->reference,
            ],
        ]);

        $auditResponse = $this->blockchainAudit->record('wallet.topup_confirmed_by_admin', [
            'wallet_transaction_id' => $walletTransaction->id,
            'wallet_id' => $walletTransaction->wallet_id,
            'amount' => (float) $walletTransaction->amount,
            'reference' => $walletTransaction->reference,
            'method' => data_get($walletTransaction->metadata, 'method'),
            'confirmed_by' => Auth::id(),
            'firefly_identity' => $walletTransaction->wallet->firefly_identity,
            'firefly_tx_id' => $fireflyResponse['tx_id'] ?? null,
            'firefly_message_id' => $fireflyResponse['message_id'] ?? null,
        ], [
            'reference' => $walletTransaction->reference,
            'user_id' => Auth::id(),
            'username' => Auth::user()?->username,
            'role' => Auth::user()?->role,
            'ip' => $request->ip(),
        ]);

        $this->appendMetadata($walletTransaction, [
            'firefly' => $fireflyResponse,
            'blockchain_audit' => $auditResponse,
        ]);

        \App\Services\SystemLogService::record('transaction', 'topup_confirmed', [
            'wallet_tx_id' => $walletTransaction->id,
            'amount' => $walletTransaction->amount,
            'reference' => $walletTransaction->reference,
            'method' => data_get($walletTransaction->metadata, 'method'),
            'confirmed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Đã xác nhận giao dịch nạp tiền thành công.');
    }

    public function fail(Request $request, WalletTransaction $walletTransaction)
    {
        $this->syncExpiredDirectTopups();
        $walletTransaction->refresh();

        if ($walletTransaction->status === 'expired' || $walletTransaction->isExpired()) {
            return back()->with('error', 'Yêu cầu nạp trực tiếp này đã hết hạn và không cần đánh dấu thất bại thêm nữa.');
        }

        if (! $walletTransaction->isPending()) {
            return back()->with('error', 'Giao dịch này đã được xử lý.');
        }

        $data = $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        $walletTransaction->fail([
            'failed_by' => Auth::id(),
            'failed_at' => now()->toDateTimeString(),
            'admin_note' => $data['admin_note'],
        ]);

        $auditResponse = $this->blockchainAudit->record('wallet.topup_failed', [
            'wallet_transaction_id' => $walletTransaction->id,
            'wallet_id' => $walletTransaction->wallet_id,
            'amount' => (float) $walletTransaction->amount,
            'reference' => $walletTransaction->reference,
            'method' => data_get($walletTransaction->metadata, 'method'),
            'failed_by' => Auth::id(),
            'admin_note' => $data['admin_note'],
        ], [
            'reference' => $walletTransaction->reference,
            'user_id' => Auth::id(),
            'username' => Auth::user()?->username,
            'role' => Auth::user()?->role,
            'ip' => $request->ip(),
        ]);

        $this->appendMetadata($walletTransaction, [
            'blockchain_audit' => $auditResponse,
        ]);

        \App\Services\SystemLogService::record('transaction', 'topup_failed', [
            'wallet_tx_id' => $walletTransaction->id,
            'amount' => $walletTransaction->amount,
            'reference' => $walletTransaction->reference,
            'method' => data_get($walletTransaction->metadata, 'method'),
            'failed_by' => Auth::id(),
            'admin_note' => $data['admin_note'],
        ]);

        return back()->with('success', 'Đã đánh dấu giao dịch nạp tiền là thất bại.');
    }

    protected function appendMetadata(WalletTransaction $walletTransaction, array $payload): void
    {
        $walletTransaction->metadata = array_merge($walletTransaction->metadata ?? [], $payload);
        $walletTransaction->save();
    }

    private function syncExpiredDirectTopups(): void
    {
        WalletTransaction::expireOverdueDirectTopups();
    }
}