<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Passport 13 narrows the auth code key to 80 characters and points client_id
 * at the now UUID keyed oauth_clients table.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->char('id', 80)->primary()->change();
            $table->foreignUuid('client_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oauth_auth_codes', function (Blueprint $table) {
            $table->string('id', 100)->primary()->change();
            $table->unsignedBigInteger('client_id')->change();
        });
    }
};
