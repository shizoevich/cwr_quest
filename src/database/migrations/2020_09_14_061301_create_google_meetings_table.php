<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGoogleMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('google_meetings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id');
            $table->integer('provider_id');
            $table->integer('appointment_id')->nullable();
            $table->string('calendar_event_external_id', 100)->unique();
            $table->string('conference_request_external_id', 100)->nullable();
            $table->string('conference_external_id', 100)->nullable();
            $table->string('conference_uri')->nullable();
            $table->boolean('conference_creation_status');
            $table->timestamp('event_starts_at')->nullable();
            $table->timestamp('event_ends_at')->nullable();
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
        Schema::dropIfExists('google_meetings');
    }
}
