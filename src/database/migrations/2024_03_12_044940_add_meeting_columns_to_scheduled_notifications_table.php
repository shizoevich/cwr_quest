<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMeetingColumnsToScheduledNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scheduled_notifications', function (Blueprint $table) {
            $table->unsignedInteger('meeting_id')->nullable()->index()->after('id');
            $table->string('meeting_type')->nullable()->after('meeting_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scheduled_notifications', function (Blueprint $table) {
            $table->dropColumn(['meeting_id', 'meeting_type']);
        });
    }
}
