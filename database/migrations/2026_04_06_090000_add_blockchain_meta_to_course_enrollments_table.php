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

        if (! Schema::hasColumn('course_enrollments', 'blockchain_meta')) {
            Schema::table('course_enrollments', function (Blueprint $table) {
                $table->json('blockchain_meta')->nullable()->after('discount_snapshot');
            });
        }
    }

    public function down(): void
    {
        if (config('database.default') === 'mongodb') {
            return;
        }

        if (Schema::hasColumn('course_enrollments', 'blockchain_meta')) {
            Schema::table('course_enrollments', function (Blueprint $table) {
                $table->dropColumn('blockchain_meta');
            });
        }
    }
};
