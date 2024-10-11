<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAllowToJoinByPhoneFieldToGoogleMeetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('google_meetings', function(Blueprint $table) {
            $table->boolean('allow_to_join_by_phone')->default(false)->after('conference_phone_pin');
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
            $table->dropColumn('allow_to_join_by_phone');
        });
    }
}
