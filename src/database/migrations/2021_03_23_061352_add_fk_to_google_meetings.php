<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToGoogleMeetings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('google_meetings', function(Blueprint $table) {
            $table->foreign('notification_id')
                ->references('id')
                ->on('scheduled_notifications');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('google_meetings', function(Blueprint $table) {
            $table->dropForeign(['notification_id']);
        });
    }
}
