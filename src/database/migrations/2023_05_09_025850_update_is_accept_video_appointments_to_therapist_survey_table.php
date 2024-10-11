<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateIsAcceptVideoAppointmentsToTherapistSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_survey', function(Blueprint $table) {
            $table->boolean('is_accept_video_appointments')->default(true)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('therapist_survey', function(Blueprint $table) {
            $table->boolean('is_accept_video_appointments')->default(false)->change();
        });
    }
}
