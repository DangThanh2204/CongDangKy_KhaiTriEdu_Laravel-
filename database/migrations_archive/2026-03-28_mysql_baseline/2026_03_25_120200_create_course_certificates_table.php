<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('enrollment_id')->constrained('course_enrollments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('certificate_no')->unique();
            $table->timestamp('issued_at');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'enrollment_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_certificates');
    }
};
