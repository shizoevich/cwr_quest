<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUphealColumnsToProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->string('upheal_user_id')->nullable()->after('is_test')->comment('external user id from upheal');
            $table->string('upheal_invite_url')->nullable()->after('upheal_user_id');
            $table->string('upheal_private_room_link')->nullable()->after('upheal_invite_url');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('upheal_user_id');
            $table->dropColumn('upheal_invite_url');
            $table->dropColumn('upheal_private_room_link');
        });
    }
}
