<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHasAccessToAllSidebarMessagesColumnToUsersMetaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_meta', function (Blueprint $table) {
            $table->boolean('has_access_to_all_sidebar_messages')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_meta', function (Blueprint $table) {
            $table->dropColumn('has_access_to_all_sidebar_messages');
        });
    }
}
