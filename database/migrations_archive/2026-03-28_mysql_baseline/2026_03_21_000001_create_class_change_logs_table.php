<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('class_change_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('course_enrollments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedBigInteger('old_class_id')->nullable();
            $table->unsignedBigInteger('new_class_id');
            $table->string('reason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_change_logs');
    }
};
