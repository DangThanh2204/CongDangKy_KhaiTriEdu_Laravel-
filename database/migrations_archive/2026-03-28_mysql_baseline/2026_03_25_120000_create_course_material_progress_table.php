<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_material_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('course_enrollments')->cascadeOnDelete();
            $table->foreignId('course_material_id')->constrained('course_materials')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_viewed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('best_quiz_score', 5, 2)->nullable();
            $table->unsignedInteger('quiz_attempts_count')->default(0);
            $table->timestamp('passed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['enrollment_id', 'course_material_id']);
            $table->index(['user_id', 'completed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_material_progress');
    }
};
