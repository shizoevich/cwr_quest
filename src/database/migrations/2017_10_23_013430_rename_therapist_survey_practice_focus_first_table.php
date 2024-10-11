<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTherapistSurveyPracticeFocusFirstTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('therapist_survey_practice_focus_first', 'therapist_survey_practice_focus');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('therapist_survey_practice_focus', 'therapist_survey_practice_focus_first');
    }
}
