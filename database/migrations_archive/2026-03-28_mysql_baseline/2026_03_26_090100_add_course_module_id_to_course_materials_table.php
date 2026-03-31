<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_materials', function (Blueprint $table) {
            if (! Schema::hasColumn('course_materials', 'course_module_id')) {
                $table->foreignId('course_module_id')
                    ->nullable()
                    ->after('course_id')
                    ->constrained('course_modules')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('course_materials', function (Blueprint $table) {
            if (Schema::hasColumn('course_materials', 'course_module_id')) {
                $table->dropConstrainedForeignId('course_module_id');
            }
        });
    }
};
