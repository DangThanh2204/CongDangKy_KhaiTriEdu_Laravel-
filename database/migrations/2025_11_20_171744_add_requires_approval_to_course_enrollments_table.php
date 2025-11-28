<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->boolean('requires_approval')->default(true)->after('status');
            $table->timestamp('enrolled_at')->nullable()->after('requires_approval');
            $table->timestamp('completed_at')->nullable()->after('enrolled_at');
        });
    }

    public function down()
    {
        Schema::table('course_enrollments', function (Blueprint $table) {
            $table->dropColumn(['requires_approval', 'enrolled_at', 'completed_at']);
        });
    }
};