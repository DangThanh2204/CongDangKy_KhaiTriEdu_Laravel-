<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            if (! Schema::hasColumn('course_enrollments', 'base_price')) {
                $table->decimal('base_price', 12, 2)->nullable()->after('seat_hold_expires_at');
            }

            if (! Schema::hasColumn('course_enrollments', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('base_price');
            }

            if (! Schema::hasColumn('course_enrollments', 'final_price')) {
                $table->decimal('final_price', 12, 2)->nullable()->after('discount_amount');
            }

            if (! Schema::hasColumn('course_enrollments', 'discount_code_id')) {
                $table->unsignedBigInteger('discount_code_id')->nullable()->after('final_price');
            }

            if (! Schema::hasColumn('course_enrollments', 'discount_snapshot')) {
                $table->json('discount_snapshot')->nullable()->after('discount_code_id');
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'base_amount')) {
                $table->decimal('base_amount', 12, 2)->nullable()->after('amount');
            }

            if (! Schema::hasColumn('payments', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('base_amount');
            }

            if (! Schema::hasColumn('payments', 'discount_code_id')) {
                $table->unsignedBigInteger('discount_code_id')->nullable()->after('discount_amount');
            }

            if (! Schema::hasColumn('payments', 'metadata')) {
                $table->json('metadata')->nullable()->after('reference');
            }
        });
    }

    public function down(): void
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $columns = [
                'base_price',
                'discount_amount',
                'final_price',
                'discount_code_id',
                'discount_snapshot',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('course_enrollments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('payments', function (Blueprint $table) {
            $columns = [
                'base_amount',
                'discount_amount',
                'discount_code_id',
                'metadata',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('payments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
