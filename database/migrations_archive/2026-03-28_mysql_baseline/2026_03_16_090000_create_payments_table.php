<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->enum('method', ['wallet', 'bank_transfer', 'qr'])->default('wallet');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->string('reference')->nullable()->unique();
            $table->timestamps();

            $table->index(['status']);
            $table->index(['user_id']);
            $table->index(['class_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};