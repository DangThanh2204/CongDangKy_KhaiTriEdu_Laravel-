<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WalletTransaction;
use App\Services\CsvExportService;
use App\Support\CollectionPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class WalletTransactionController extends Controller
{
    public function index(Request $request)
    {
        $this->syncExpiredDirectTopups();

        $fromDate = $request->get('from_date');
        $toDate = $request->get('to_date');

        $transactions = $this->sortTransactionsForDisplay(
            $this->filteredQuery($request)->get()
        );

        $transactions = CollectionPaginator::paginate(
            $transactions,
            20,
            max((int) $request->integer('page', 1), 1),
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ],
        );

        return view('admin.wallet-transactions.index', compact('transactions', 'fromDate', 'toDate'));
    }

    public function export(Request $request, CsvExportService $csvExportService)
    {
        $this->syncExpiredDirectTopups();

        $transactions = $this->sortTransactionsForDisplay(
            $this->filteredQuery($request)->get()
        );

        return $csvExportService->download(
            'wallet-transactions-' . now()->format('Y-m-d-His') . '.csv',
            ['ID', 'Hoc vien', 'Email', 'So tien', 'Ma giao dich', 'Phuong thuc', 'Trang thai', 'Bat dau', 'Het han', 'Het han luc'],
            $transactions->map(function (WalletTransaction $transaction) {
                return [
                    $transaction->id,
                    $transaction->wallet->user->fullname ?? $transaction->wallet->user->username ?? '',
                    $transaction->wallet->user->email ?? '',
                    $transaction->amount,
                    $transaction->reference,
                    $transaction->method_label,
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

    protected function sortTransactionsForDisplay(Collection $transactions): Collection
    {
        return $transactions->sort(function (WalletTransaction $left, WalletTransaction $right): int {
            $priority = [
                'pending' => 0,
                'expired' => 1,
            ];

            $leftPriority = $priority[$left->status] ?? 2;
            $rightPriority = $priority[$right->status] ?? 2;

            if ($leftPriority !== $rightPriority) {
                return $leftPriority <=> $rightPriority;
            }

            return ($right->created_at?->format('Y-m-d H:i:s.u') ?? '') <=> ($left->created_at?->format('Y-m-d H:i:s.u') ?? '');
        })->values();
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
            return back()->with('error', 'Yeu cau nap tien thu cong nay da het han, khong the xac nhan nua.');
        }

        if (! $walletTransaction->isPending()) {
            return back()->with('error', 'Giao dich nay da duoc xu ly.');
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

        \App\Services\SystemLogService::record('transaction', 'topup_confirmed', [
            'wallet_tx_id' => $walletTransaction->id,
            'amount' => $walletTransaction->amount,
            'reference' => $walletTransaction->reference,
            'method' => data_get($walletTransaction->metadata, 'method'),
            'confirmed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Da xac nhan giao dich nap tien thanh cong.');
    }

    public function fail(Request $request, WalletTransaction $walletTransaction)
    {
        $this->syncExpiredDirectTopups();
        $walletTransaction->refresh();

        if ($walletTransaction->status === 'expired' || $walletTransaction->isExpired()) {
            return back()->with('error', 'Yeu cau nap tien thu cong nay da het han va khong can danh dau that bai them nua.');
        }

        if (! $walletTransaction->isPending()) {
            return back()->with('error', 'Giao dich nay da duoc xu ly.');
        }

        $data = $request->validate([
            'admin_note' => 'required|string|max:500',
        ]);

        $walletTransaction->fail([
            'failed_by' => Auth::id(),
            'failed_at' => now()->toDateTimeString(),
            'admin_note' => $data['admin_note'],
        ]);

        \App\Services\SystemLogService::record('transaction', 'topup_failed', [
            'wallet_tx_id' => $walletTransaction->id,
            'amount' => $walletTransaction->amount,
            'reference' => $walletTransaction->reference,
            'method' => data_get($walletTransaction->metadata, 'method'),
            'failed_by' => Auth::id(),
            'admin_note' => $data['admin_note'],
        ]);

        return back()->with('success', 'Da danh dau giao dich nap tien la that bai.');
    }

    private function syncExpiredDirectTopups(): void
    {
        WalletTransaction::expireOverdueDirectTopups();
    }
}
