<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSocialProviderUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable();
            }
        });
        Schema::create('social_providers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('provider')->index();
            $table->string('provider_id')->index();
            $table->timestamps();
        });

        // Fix for migration error:
        /*  SQLSTATE[42000]: Syntax error or access violation: 1072 Key column 'user_id' doesn't exist in table (SQL: alter table `social_providers` add constraint `social_providers_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade) */
        Schema::table('social_providers', function (Blueprint $table) {
            $table->foreignId('user_id')->after('provider_id')->constrained()->onDelete('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar');
        });
        Schema::dropIfExists('social_providers');
    }
}
