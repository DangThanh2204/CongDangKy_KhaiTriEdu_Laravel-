<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('fullname');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('otp')->nullable();
            $table->enum('role', ['admin', 'staff', 'student', 'instructor'])->default('student');
            $table->boolean('is_verified')->default(false);
            $table->rememberToken();
            $table->timestamps();
            
            // Thêm index cho các trường thường dùng
            $table->index('role');
            $table->index('is_verified');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users');
    }
};