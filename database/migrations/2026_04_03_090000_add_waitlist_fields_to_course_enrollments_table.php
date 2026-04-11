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

        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->timestamp('waitlist_joined_at')->nullable()->after('cancelled_at');
            $table->timestamp('waitlist_promoted_at')->nullable()->after('waitlist_joined_at');
            $table->timestamp('seat_hold_expires_at')->nullable()->after('waitlist_promoted_at');

            $table->index(['class_id', 'status', 'waitlist_joined_at'], 'course_enrollments_waitlist_queue_index');
            $table->index(['class_id', 'status', 'seat_hold_expires_at'], 'course_enrollments_seat_hold_index');
        });
    }

    public function down(): void
    {
        if (config('database.default') === 'mongodb') {
            return;
        }

        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropIndex('course_enrollments_waitlist_queue_index');
            $table->dropIndex('course_enrollments_seat_hold_index');

            $table->dropColumn([
                'waitlist_joined_at',
                'waitlist_promoted_at',
                'seat_hold_expires_at',
            ]);
        });
    }
};
