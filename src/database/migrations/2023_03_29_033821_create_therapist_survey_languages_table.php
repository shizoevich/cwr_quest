<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTherapistSurveyLanguagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_survey_languages', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('label');
            $table->string('tridiuum_value')->nullable();
        });

        Schema::create('therapist_has_languages', function(Blueprint $table)
        {
            $table->unsignedInteger('therapist_id');
            $table->unsignedInteger('language_id');

            $table->foreign('therapist_id')
                ->references('id')
                ->on('therapist_survey')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('language_id')
                ->references('id')
                ->on('therapist_survey_languages')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('therapist_has_languages');
        Schema::dropIfExists('therapist_survey_languages');
    }
}
