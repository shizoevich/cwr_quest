<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotificationIdToGooglemertings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('google_meetings', function(Blueprint $table) {
            $table->unsignedInteger('notification_id')
                ->nullable()
                ->default(null)
                ->after('appointment_id')
                ->comment('scheduled_notifications.id');
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
            $table->dropColumn('notification_id');
        });
    }
}
