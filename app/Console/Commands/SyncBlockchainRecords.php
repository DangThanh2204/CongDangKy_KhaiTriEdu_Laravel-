<?php

namespace App\Console\Commands;

use App\Services\BlockchainSyncService;
use Illuminate\Console\Command;

class SyncBlockchainRecords extends Command
{
    protected $signature = 'blockchain:sync-pending {--limit=50 : Maximum number of certificates and transactions to process per batch}';

    protected $description = 'Sync pending certificates and wallet transactions to Hyperledger FireFly';

    public function handle(BlockchainSyncService $syncService): int
    {
        $this->prepareUtf8ConsoleOutput();

        $limit = max((int) $this->option('limit'), 1);
        $summary = $syncService->syncPendingRecords($limit);

        $this->line('Certificates synced: ' . ($summary['certificates_synced'] ?? 0));
        $this->line('Transactions synced: ' . ($summary['transactions_synced'] ?? 0));
        $this->line('Failed: ' . ($summary['failed'] ?? 0));

        if (! empty($summary['message'])) {
            $this->info($this->formatConsoleMessage($summary));
        }

        return ($summary['success'] ?? false) ? self::SUCCESS : self::FAILURE;
    }

    protected function prepareUtf8ConsoleOutput(): void
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return;
        }

        if (function_exists('sapi_windows_cp_set')) {
            @sapi_windows_cp_set(65001);
        }
    }

    protected function formatConsoleMessage(array $summary): string
    {
        $message = (string) ($summary['message'] ?? '');

        if (PHP_OS_FAMILY !== 'Windows') {
            return $message;
        }

        $certificatesSynced = (int) ($summary['certificates_synced'] ?? 0);
        $transactionsSynced = (int) ($summary['transactions_synced'] ?? 0);
        $failed = (int) ($summary['failed'] ?? 0);
        $success = (bool) ($summary['success'] ?? false);

        if (! $success && $certificatesSynced === 0 && $transactionsSynced === 0 && $failed === 0) {
            return 'FireFly chua duoc cau hinh, chua the dong bo blockchain.';
        }

        if ($success && $certificatesSynced === 0 && $transactionsSynced === 0 && $failed === 0) {
            return 'Khong co ban ghi nao can dong bo them voi FireFly.';
        }

        if ($success && $failed === 0) {
            return 'Da dong bo blockchain thanh cong.';
        }

        return 'Mot phan ban ghi chua dong bo duoc voi FireFly. Vui long kiem tra lai cau hinh hoac log.';
    }
}
