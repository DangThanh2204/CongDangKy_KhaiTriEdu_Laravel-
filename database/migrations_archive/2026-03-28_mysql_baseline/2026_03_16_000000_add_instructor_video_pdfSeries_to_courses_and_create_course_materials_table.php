<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'video_url')) {
                $table->string('video_url')->nullable();
            }
            if (! Schema::hasColumn('courses', 'pdf_path')) {
                $table->string('pdf_path')->nullable();
            }
            if (! Schema::hasColumn('courses', 'series_key')) {
                $table->string('series_key')->nullable()->index();
            }
        });

        Schema::create('course_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['video', 'pdf', 'assignment', 'quiz'])->default('video');
            $table->string('title')->nullable();
            $table->text('content')->nullable();
            $table->string('file_path')->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_materials');

        Schema::table('courses', function (Blueprint $table) {
            if (Schema::hasColumn('courses', 'video_url')) {
                $table->dropColumn('video_url');
            }
            if (Schema::hasColumn('courses', 'pdf_path')) {
                $table->dropColumn('pdf_path');
            }
            if (Schema::hasColumn('courses', 'series_key')) {
                $table->dropColumn('series_key');
            }
        });
    }
};