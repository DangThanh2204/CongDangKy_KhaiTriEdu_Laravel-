<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Change enum to string to allow more methods like 'cash'
        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->string('method', 50)->nullable()->change();
            });
        }
    }

    public function down()
    {
        // revert to original enum if needed (left intentionally simple)
    }
};
