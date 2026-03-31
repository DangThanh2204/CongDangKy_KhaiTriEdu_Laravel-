<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id')->index();
            $table->string('weekday', 5)->comment('2..7 or CN');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamps();

            $table->foreign('class_id')->references('id')->on('classes')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('class_schedules');
    }
};
