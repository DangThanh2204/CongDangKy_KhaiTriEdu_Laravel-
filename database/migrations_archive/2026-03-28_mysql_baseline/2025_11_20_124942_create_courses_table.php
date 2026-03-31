<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description');
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('sale_price', 10, 2)->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('banner_image')->nullable();
            $table->enum('level', ['beginner', 'intermediate', 'advanced', 'all'])->default('beginner');
            $table->integer('duration')->default(0); // in minutes
            $table->integer('lessons_count')->default(0);
            $table->integer('students_count')->default(0);
            $table->decimal('rating', 2, 1)->default(0);
            $table->integer('total_rating')->default(0);
            $table->foreignId('category_id')->constrained('course_categories')->onDelete('cascade');
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_popular')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
};