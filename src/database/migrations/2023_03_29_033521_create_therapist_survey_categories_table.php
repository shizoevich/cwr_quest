<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTherapistSurveyCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_survey_patient_categories', function (Blueprint $table) {
            $table->unsignedInteger('id',true);
            $table->string('label');
            $table->string('tridiuum_value')->nullable();
        });

        Schema::create('therapist_has_patient_categories', function(Blueprint $table)
        {
            $table->unsignedInteger('therapist_id');
            $table->unsignedInteger('patient_category_id');

            $table->foreign('therapist_id')
                ->references('id')
                ->on('therapist_survey')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('patient_category_id')
                ->references('id')
                ->on('therapist_survey_patient_categories')
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
        Schema::dropIfExists('therapist_has_patient_categories');
        Schema::dropIfExists('therapist_survey_patient_categories');
    }
}
