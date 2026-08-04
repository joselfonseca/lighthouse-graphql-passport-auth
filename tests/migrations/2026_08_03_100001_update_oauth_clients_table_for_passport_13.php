<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Passport 13 reshapes oauth_clients: clients are keyed by a UUID, the owning
 * model is polymorphic, redirect URIs and grant types are stored as arrays.
 *
 * The primary key changes type, which no database can express as an ALTER, so
 * the table is rebuilt. This migration is written for the test database, which
 * is always created empty; an application holding existing clients should
 * follow Passport's own upgrade guide, which keeps integer keys via
 * Passport::$clientUuids = false rather than retrofitting UUIDs onto rows that
 * oauth_access_tokens and oauth_auth_codes already reference.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('oauth_clients_passport_13', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Named explicitly so the rebuild does not carry the temporary
            // table name into the index.
            $table->nullableMorphs('owner', 'oauth_clients_owner_type_owner_id_index');
            $table->string('name');
            $table->string('secret')->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect_uris');
            $table->text('grant_types');
            $table->boolean('revoked');
            $table->timestamps();
        });

        Schema::drop('oauth_clients');
        Schema::rename('oauth_clients_passport_13', 'oauth_clients');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('oauth_clients_passport_12', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index('oauth_clients_user_id_index');
            $table->string('name');
            $table->string('secret', 100)->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect');
            $table->boolean('personal_access_client');
            $table->boolean('password_client');
            $table->boolean('revoked');
            $table->timestamps();
        });

        Schema::drop('oauth_clients');
        Schema::rename('oauth_clients_passport_12', 'oauth_clients');
    }
};
