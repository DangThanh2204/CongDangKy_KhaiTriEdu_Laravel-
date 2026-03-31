<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistant_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assistant_conversation_id')->constrained()->cascadeOnDelete();
            $table->string('role', 20);
            $table->text('message');
            $table->json('recommended_courses')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['assistant_conversation_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistant_messages');
    }
};
