<?php

namespace App\Services;

use App\Models\CourseCertificate;
use App\Models\WalletTransaction;

class BlockchainSyncService
{
    public function __construct(
        protected FireflyService $firefly,
        protected BlockchainAuditService $blockchainAudit,
        protected CertificateBlockchainService $certificateBlockchain,
    ) {
    }

    public function syncPendingRecords(int $limit = 50): array
    {
        if (! $this->firefly->isConfigured()) {
            return [
                'success' => false,
                'message' => 'FireFly ÄÃ£ ÄÆ°á»£c cáº¥u hÃ¬nh, chÆ°a thá» Äá»ng bá» blockchain.',
                'certificates_synced' => 0,
                'transactions_synced' => 0,
                'failed' => 0,
            ];
        }

        $summary = [
            'success' => true,
            'message' => 'ÄÃ£ Äá»ng bá» blockchain thÃ nh cÃ´ng.',
            'certificates_synced' => 0,
            'transactions_synced' => 0,
            'failed' => 0,
        ];

        $certificates = CourseCertificate::query()
            ->with([
                'course:id,title,learning_type,delivery_mode',
                'user:id,fullname,username,email,role',
                'enrollment:id,class_id,completed_at',
                'enrollment.courseClass:id,name,start_date',
            ])
            ->latest('issued_at')
            ->get()
            ->filter(fn (CourseCertificate $certificate) => ! (bool) data_get($certificate->meta, 'blockchain_audit.success', false))
            ->take($limit);

        foreach ($certificates as $certificate) {
            try {
                $fresh = $this->certificateBlockchain->ensureAnchored($certificate);

                if ((bool) data_get($fresh->meta, 'blockchain_audit.success', false)) {
                    $summary['certificates_synced']++;
                } else {
                    $summary['failed']++;
                }
            } catch (\Throwable $exception) {
                report($exception);
                $summary['failed']++;
            }
        }

        $transactions = WalletTransaction::query()
            ->with('wallet.user:id,fullname,username,email,role')
            ->where('status', 'completed')
            ->latest('created_at')
            ->get()
            ->filter(fn (WalletTransaction $transaction) => ! (bool) data_get($transaction->metadata, 'blockchain_audit.success', false))
            ->take($limit);

        foreach ($transactions as $transaction) {
            $result = $this->syncWalletTransaction($transaction);

            if ($result['success']) {
                $summary['transactions_synced']++;
            } else {
                $summary['failed']++;
            }
        }

        if ($summary['certificates_synced'] === 0 && $summary['transactions_synced'] === 0 && $summary['failed'] === 0) {
            $summary['message'] = 'KhÃ´ng cÃ³ báº£n ghi nÃ o cáº§n Äá»ng bá» thÃªm vá»i FireFly.';
        } elseif ($summary['failed'] > 0) {
            $summary['success'] = false;
            $summary['message'] = 'Má»t pháº§n báº£n ghi chÆ°a Äá»ng bá» ÄÆ°á»£c vá»i FireFly. Vui lÃ²ng kiá»m tra láº¡i cáº¥u hÃ¬nh hoáº·c log.';
        }

        return $summary;
    }

    public function syncWalletTransaction(WalletTransaction $transaction): array
    {
        $transaction->loadMissing('wallet.user');

        if (! $transaction->wallet || ! $transaction->wallet->firefly_identity) {
            return [
                'success' => false,
                'message' => 'VÃ­ chÆ°a cÃ³ FireFly identity.',
            ];
        }

        $reference = $transaction->reference ?: ('WTX-' . $transaction->id);
        $method = data_get($transaction->metadata, 'method', 'wallet');
        $fireflyResponse = null;
        $auditResponse = null;

        try {
            if ($transaction->type === 'deposit') {
                $fireflyResponse = $this->firefly->mint($transaction->wallet->firefly_identity, (float) $transaction->amount, [
                    'reference' => $reference,
                    'data' => [
                        'type' => 'wallet_topup_sync',
                        'wallet_transaction_id' => $transaction->id,
                        'wallet_id' => $transaction->wallet_id,
                        'method' => $method,
                        'amount' => (float) $transaction->amount,
                        'reference' => $reference,
                    ],
                ]);

                $auditResponse = $this->blockchainAudit->record('wallet.transaction_synced', [
                    'wallet_transaction_id' => $transaction->id,
                    'wallet_id' => $transaction->wallet_id,
                    'type' => $transaction->type,
                    'method' => $method,
                    'amount' => (float) $transaction->amount,
                    'reference' => $reference,
                    'firefly_identity' => $transaction->wallet->firefly_identity,
                    'firefly_tx_id' => $fireflyResponse['tx_id'] ?? null,
                    'firefly_message_id' => $fireflyResponse['message_id'] ?? null,
                ], [
                    'reference' => $reference,
                    'user_id' => $transaction->wallet->user_id,
                    'username' => $transaction->wallet->user->username ?? null,
                    'role' => $transaction->wallet->user->role ?? null,
                ]);
            } else {
                $platformIdentity = $this->firefly->getPlatformIdentity();
                $fireflyResponse = $this->firefly->transfer(
                    $transaction->wallet->firefly_identity,
                    $platformIdentity,
                    (float) $transaction->amount,
                    [
                        'reference' => $reference,
                        'data' => [
                            'type' => 'wallet_spending_sync',
                            'wallet_transaction_id' => $transaction->id,
                            'wallet_id' => $transaction->wallet_id,
                            'method' => $method,
                            'amount' => (float) $transaction->amount,
                            'reference' => $reference,
                        ],
                    ]
                );

                $auditResponse = $this->blockchainAudit->record('wallet.transaction_synced', [
                    'wallet_transaction_id' => $transaction->id,
                    'wallet_id' => $transaction->wallet_id,
                    'type' => $transaction->type,
                    'method' => $method,
                    'amount' => (float) $transaction->amount,
                    'reference' => $reference,
                    'wallet_firefly_identity' => $transaction->wallet->firefly_identity,
                    'platform_identity' => $platformIdentity,
                    'firefly_tx_id' => $fireflyResponse['tx_id'] ?? null,
                    'firefly_message_id' => $fireflyResponse['message_id'] ?? null,
                ], [
                    'reference' => $reference,
                    'user_id' => $transaction->wallet->user_id,
                    'username' => $transaction->wallet->user->username ?? null,
                    'role' => $transaction->wallet->user->role ?? null,
                ]);
            }
        } catch (\Throwable $exception) {
            report($exception);

            return [
                'success' => false,
                'message' => $exception->getMessage(),
            ];
        }

        $transaction->update([
            'reference' => $reference,
            'metadata' => array_merge($transaction->metadata ?? [], array_filter([
                'firefly' => $fireflyResponse,
                'blockchain_audit' => $auditResponse,
                'blockchain_synced_at' => now()->toDateTimeString(),
            ])),
        ]);

        return [
            'success' => (bool) data_get($auditResponse, 'success', false),
            'message' => data_get($auditResponse, 'message'),
        ];
    }
}
