<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (config('database.default') === 'mongodb') {
            return;
        }

        if (Schema::hasColumn('wallets', 'firefly_identity')) {
            try {
                Schema::table('wallets', function (Blueprint $table) {
                    $table->dropUnique('wallets_firefly_identity_unique');
                });
            } catch (\Throwable $exception) {
            }

            Schema::table('wallets', function (Blueprint $table) {
                $table->dropColumn('firefly_identity');
            });
        }

        if (Schema::hasColumn('course_enrollments', 'blockchain_meta')) {
            Schema::table('course_enrollments', function (Blueprint $table) {
                $table->dropColumn('blockchain_meta');
            });
        }
    }

    public function down(): void
    {
        if (config('database.default') === 'mongodb') {
            return;
        }

        if (! Schema::hasColumn('wallets', 'firefly_identity')) {
            Schema::table('wallets', function (Blueprint $table) {
                $table->string('firefly_identity')->nullable()->unique()->after('balance');
            });
        }

        if (! Schema::hasColumn('course_enrollments', 'blockchain_meta')) {
            Schema::table('course_enrollments', function (Blueprint $table) {
                $table->json('blockchain_meta')->nullable()->after('discount_snapshot');
            });
        }
    }
};
