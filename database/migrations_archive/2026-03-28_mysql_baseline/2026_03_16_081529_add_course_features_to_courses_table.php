<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            // Thumbnail cho khóa học (nếu chưa có)
            if (!Schema::hasColumn('courses', 'thumbnail')) {
                $table->string('thumbnail')->nullable();
            }

            // Thông tin lịch học
            $table->enum('learning_type', ['online', 'offline', 'hybrid'])->default('online')->after('status');
            $table->text('announcement')->nullable()->after('learning_type'); // Thông báo cho học viên

            // Cài đặt quiz mặc định
            $table->boolean('has_default_quiz')->default(false)->after('announcement');
            $table->json('default_quiz_data')->nullable()->after('has_default_quiz');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'thumbnail',
                'learning_type',
                'announcement',
                'has_default_quiz',
                'default_quiz_data'
            ]);
        });
    }
};