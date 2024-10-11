<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsInitialFieldToGoogleMeetingCallLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('google_meeting_call_logs', function(Blueprint $table) {
            $table->boolean('is_initial')->default(false)->after('ip');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('google_meeting_call_logs', function(Blueprint $table) {
            $table->dropColumn('is_initial');
        });
    }
}
