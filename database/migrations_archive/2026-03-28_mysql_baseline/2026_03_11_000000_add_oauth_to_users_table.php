<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // OAuth provider fields
            $table->string('google_id')->nullable()->unique()->after('email');
            $table->string('facebook_id')->nullable()->unique()->after('google_id');
            $table->string('provider')->nullable()->after('facebook_id');
            $table->string('provider_id')->nullable()->after('provider');
            
            // Make password nullable for OAuth users
            $table->string('password')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['google_id', 'facebook_id', 'provider', 'provider_id']);
            // Note: Reverting password back to non-nullable might cause issues
            // if there are OAuth-only users, so we'll keep it as is
        });
    }
};
