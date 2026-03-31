<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('course_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('instructor_id')->constrained('users')->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned()->default(5);
            $table->tinyInteger('instructor_rating')->unsigned()->default(5);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_reviews');
    }
};
