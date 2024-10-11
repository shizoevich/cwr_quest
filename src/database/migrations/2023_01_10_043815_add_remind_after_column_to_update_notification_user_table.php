<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRemindAfterColumnToUpdateNotificationUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('update_notification_user', function (Blueprint $table) {
            $table->datetime('remind_after')->nullable()->after('viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('update_notification_user', function (Blueprint $table) {
            $table->dropColumn('remind_after');
        });
    }
}
