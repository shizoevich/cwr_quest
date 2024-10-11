<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToGoogleMeetingCallLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('google_meeting_call_logs', function(Blueprint $table) {
            $table->foreign('google_meeting_id')
                ->references('id')
                ->on('google_meetings')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
        
        });
    }
}
