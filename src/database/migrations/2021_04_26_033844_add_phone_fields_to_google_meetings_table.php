<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneFieldsToGoogleMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('google_meetings', function(Blueprint $table) {
            $table->string('conference_phone', 64)->nullable()->after('conference_uri');
            $table->string('conference_phone_pin', 64)->nullable()->after('conference_phone');
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
            $table->dropColumn(['conference_phone', 'conference_phone_pin',]);
        });
    }
}
