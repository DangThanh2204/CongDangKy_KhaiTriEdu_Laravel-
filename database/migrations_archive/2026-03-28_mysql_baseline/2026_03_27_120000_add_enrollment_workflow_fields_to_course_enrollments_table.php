<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('course_enrollments', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('enrolled_at');
            }

            if (! Schema::hasColumn('course_enrollments', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('course_enrollments', 'cancelled_at')) {
                $table->timestamp('cancelled_at')->nullable()->after('rejected_at');
            }

            if (! Schema::hasColumn('course_enrollments', 'notes')) {
                $table->text('notes')->nullable()->after('cancelled_at');
            }
        });

        $this->updateStatusColumn([
            'pending',
            'approved',
            'rejected',
            'cancelled',
            'completed',
        ]);
    }

    public function down(): void
    {
        $this->updateStatusColumn([
            'pending',
            'approved',
            'rejected',
            'completed',
        ]);

        Schema::table('course_enrollments', function (Blueprint $table) {
            if (Schema::hasColumn('course_enrollments', 'notes')) {
                $table->dropColumn('notes');
            }

            if (Schema::hasColumn('course_enrollments', 'cancelled_at')) {
                $table->dropColumn('cancelled_at');
            }

            if (Schema::hasColumn('course_enrollments', 'rejected_at')) {
                $table->dropColumn('rejected_at');
            }

            if (Schema::hasColumn('course_enrollments', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
        });
    }

    private function updateStatusColumn(array $statuses): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $default = $statuses[0] ?? 'pending';
        $quotedStatuses = implode(', ', array_map(fn (string $status) => "'{$status}'", $statuses));

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement(sprintf(
                'ALTER TABLE course_enrollments MODIFY status ENUM(%s) NOT NULL DEFAULT %s',
                $quotedStatuses,
                DB::getPdo()->quote($default)
            ));

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE course_enrollments DROP CONSTRAINT IF EXISTS course_enrollments_status_check');
            DB::statement('ALTER TABLE course_enrollments ALTER COLUMN status TYPE VARCHAR(20)');
            DB::statement(sprintf(
                'ALTER TABLE course_enrollments ADD CONSTRAINT course_enrollments_status_check CHECK (status IN (%s))',
                $quotedStatuses
            ));
            DB::statement(sprintf(
                'ALTER TABLE course_enrollments ALTER COLUMN status SET DEFAULT %s',
                DB::getPdo()->quote($default)
            ));
        }
    }
};