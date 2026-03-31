<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistant_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id', 120)->index();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'session_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistant_conversations');
    }
};
