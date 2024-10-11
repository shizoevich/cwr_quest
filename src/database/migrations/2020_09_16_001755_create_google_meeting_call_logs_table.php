<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoogleMeetingCallLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_meeting_call_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('google_meeting_id');
            $table->integer('provider_id')->nullable();
            $table->string('external_id', 100)->unique();
            $table->unsignedInteger('duration')->comment('Call duration in seconds');
            $table->boolean('is_external');
            $table->text('caller_name')->nullable();
            $table->ipAddress('ip')->nullable();
            $table->timestamp('call_starts_at')->nullable();
            $table->timestamp('call_ends_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('google_meeting_call_logs');
    }
}
