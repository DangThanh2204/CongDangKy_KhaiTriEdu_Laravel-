<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('course_materials')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE course_materials MODIFY COLUMN type ENUM('video', 'pdf', 'assignment', 'quiz', 'meeting') NOT NULL DEFAULT 'video'"
            );
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('course_materials')) {
            return;
        }

        if (DB::getDriverName() === 'mysql') {
            DB::table('course_materials')
                ->where('type', 'meeting')
                ->update(['type' => 'assignment']);

            DB::statement(
                "ALTER TABLE course_materials MODIFY COLUMN type ENUM('video', 'pdf', 'assignment', 'quiz') NOT NULL DEFAULT 'video'"
            );
        }
    }
};
