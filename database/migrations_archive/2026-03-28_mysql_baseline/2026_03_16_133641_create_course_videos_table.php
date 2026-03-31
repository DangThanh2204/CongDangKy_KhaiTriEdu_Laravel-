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
        Schema::create('course_videos', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('lesson_id')->nullable()->constrained('course_materials')->onDelete('cascade');
            $table->string('original_filename');
            $table->string('video_path'); // path to original video file
            $table->string('hls_playlist_path')->nullable(); // path to HLS playlist (.m3u8)
            $table->string('hls_segments_path')->nullable(); // path to HLS segments directory
            $table->integer('duration')->nullable(); // in seconds
            $table->string('video_codec')->nullable();
            $table->integer('width')->nullable();
            $table->integer('height')->nullable();
            $table->bigInteger('file_size')->nullable(); // in bytes
            $table->enum('processing_status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->text('processing_error')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // additional video metadata
            $table->timestamps();

            $table->index(['course_id', 'order']);
            $table->index(['lesson_id', 'order']);
            $table->index('processing_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_videos');
    }
};
