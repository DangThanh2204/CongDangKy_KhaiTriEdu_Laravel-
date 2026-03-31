<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('system_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('category', 50)->index(); // security, transaction, system
            $table->string('action', 100)->index();
            $table->text('details')->nullable(); // JSON or text
            $table->string('reference')->nullable()->index(); // payment/tx reference
            $table->string('ip', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();

            $table->index(['category', 'action']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('system_logs');
    }
};
