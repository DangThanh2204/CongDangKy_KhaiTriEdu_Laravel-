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
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->onDelete('cascade');
            $table->text('question_text');
            $table->enum('question_type', ['multiple_choice', 'true_false', 'short_answer', 'essay'])->default('multiple_choice');
            $table->json('options')->nullable(); // for multiple choice options
            $table->json('correct_answers'); // array of correct answers
            $table->text('explanation')->nullable(); // explanation for correct answer
            $table->integer('points')->default(1);
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['quiz_id', 'order']);
            $table->index('question_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
