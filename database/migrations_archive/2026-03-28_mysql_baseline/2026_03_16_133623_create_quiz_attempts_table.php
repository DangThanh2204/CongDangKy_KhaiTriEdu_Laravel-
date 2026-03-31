<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->integer('attempt_number')->default(1);
            $table->integer('total_questions');
            $table->integer('correct_answers')->default(0);
            $table->integer('total_points')->default(0);
            $table->integer('earned_points')->default(0);
            $table->decimal('percentage_score', 5, 2)->default(0);
            $table->enum('status', ['in_progress', 'completed', 'timed_out'])->default('in_progress');
            $table->timestamp('started_at');
            $table->timestamp('completed_at')->nullable();
            $table->integer('time_taken')->nullable(); // in seconds
            $table->json('answers_summary')->nullable(); // summary of answers for quick view
            $table->timestamps();

            $table->index(['user_id', 'quiz_id']);
            $table->index(['quiz_id', 'status']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
