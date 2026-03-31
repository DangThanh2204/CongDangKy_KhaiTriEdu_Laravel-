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
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('lesson_id')->nullable()->constrained('course_materials')->onDelete('cascade');
            $table->enum('type', ['pre_test', 'post_test', 'practice', 'exam'])->default('practice');
            $table->integer('time_limit')->nullable(); // in minutes
            $table->integer('passing_score')->default(70); // percentage
            $table->integer('max_attempts')->default(3);
            $table->boolean('is_active')->default(true);
            $table->boolean('shuffle_questions')->default(false);
            $table->boolean('show_results')->default(true);
            $table->json('settings')->nullable(); // additional settings
            $table->timestamps();

            $table->index(['course_id', 'type']);
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
