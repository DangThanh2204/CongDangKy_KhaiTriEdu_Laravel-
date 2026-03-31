<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('wallet_transactions', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('reference');
            }

            if (! Schema::hasColumn('wallet_transactions', 'expired_at')) {
                $table->timestamp('expired_at')->nullable()->after('expires_at');
            }
        });

        $this->updateStatusColumn([
            'pending',
            'completed',
            'failed',
            'expired',
        ]);

        $this->backfillDirectTopupExpiry();
        $this->markOverdueTransactionsAsExpired();
    }

    public function down(): void
    {
        $this->convertExpiredTransactionsToFailed();

        $this->updateStatusColumn([
            'pending',
            'completed',
            'failed',
        ]);

        Schema::table('wallet_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_transactions', 'expired_at')) {
                $table->dropColumn('expired_at');
            }

            if (Schema::hasColumn('wallet_transactions', 'expires_at')) {
                $table->dropColumn('expires_at');
            }
        });
    }

    private function backfillDirectTopupExpiry(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement(<<<'SQL'
                UPDATE wallet_transactions
                SET expires_at = IFNULL(expires_at, DATE_ADD(created_at, INTERVAL 48 HOUR))
                WHERE type = 'deposit'
                  AND JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.method')) = 'direct'
            SQL);

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement(<<<'SQL'
                UPDATE wallet_transactions
                SET expires_at = COALESCE(expires_at, created_at + INTERVAL '48 hours')
                WHERE type = 'deposit'
                  AND COALESCE(metadata->>'method', '') = 'direct'
            SQL);

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement(<<<'SQL'
                UPDATE wallet_transactions
                SET expires_at = COALESCE(expires_at, datetime(created_at, '+48 hours'))
                WHERE type = 'deposit'
                  AND json_extract(metadata, '$.method') = 'direct'
            SQL);
        }
    }

    private function markOverdueTransactionsAsExpired(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement(<<<'SQL'
                UPDATE wallet_transactions
                SET status = 'expired',
                    expired_at = IFNULL(expired_at, NOW())
                WHERE type = 'deposit'
                  AND status = 'pending'
                  AND JSON_UNQUOTE(JSON_EXTRACT(metadata, '$.method')) = 'direct'
                  AND expires_at IS NOT NULL
                  AND expires_at <= NOW()
            SQL);

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement(<<<'SQL'
                UPDATE wallet_transactions
                SET status = 'expired',
                    expired_at = COALESCE(expired_at, NOW())
                WHERE type = 'deposit'
                  AND status = 'pending'
                  AND COALESCE(metadata->>'method', '') = 'direct'
                  AND expires_at IS NOT NULL
                  AND expires_at <= NOW()
            SQL);

            return;
        }

        if ($driver === 'sqlite') {
            DB::statement(<<<'SQL'
                UPDATE wallet_transactions
                SET status = 'expired',
                    expired_at = COALESCE(expired_at, datetime('now'))
                WHERE type = 'deposit'
                  AND status = 'pending'
                  AND json_extract(metadata, '$.method') = 'direct'
                  AND expires_at IS NOT NULL
                  AND expires_at <= datetime('now')
            SQL);
        }
    }

    private function convertExpiredTransactionsToFailed(): void
    {
        DB::table('wallet_transactions')
            ->where('status', 'expired')
            ->update([
                'status' => 'failed',
                'expired_at' => null,
            ]);
    }

    private function updateStatusColumn(array $statuses): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $default = $statuses[0] ?? 'pending';
        $quotedStatuses = implode(', ', array_map(fn (string $status) => "'{$status}'", $statuses));

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement(sprintf(
                'ALTER TABLE wallet_transactions MODIFY status ENUM(%s) NOT NULL DEFAULT %s',
                $quotedStatuses,
                DB::getPdo()->quote($default)
            ));

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE wallet_transactions DROP CONSTRAINT IF EXISTS wallet_transactions_status_check');
            DB::statement('ALTER TABLE wallet_transactions ALTER COLUMN status TYPE VARCHAR(20)');
            DB::statement(sprintf(
                'ALTER TABLE wallet_transactions ADD CONSTRAINT wallet_transactions_status_check CHECK (status IN (%s))',
                $quotedStatuses
            ));
            DB::statement(sprintf(
                'ALTER TABLE wallet_transactions ALTER COLUMN status SET DEFAULT %s',
                DB::getPdo()->quote($default)
            ));
        }
    }
};