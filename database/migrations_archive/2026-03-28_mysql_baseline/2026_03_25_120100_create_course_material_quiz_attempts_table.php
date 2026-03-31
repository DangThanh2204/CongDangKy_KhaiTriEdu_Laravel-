<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_material_quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('course_enrollments')->cascadeOnDelete();
            $table->foreignId('course_material_id')->constrained('course_materials')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('attempt_number')->default(1);
            $table->unsignedInteger('total_questions')->default(0);
            $table->unsignedInteger('correct_answers')->default(0);
            $table->decimal('score_percent', 5, 2)->default(0);
            $table->boolean('is_passed')->default(false);
            $table->json('answers_summary')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['course_material_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_material_quiz_attempts');
    }
};
