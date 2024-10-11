<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTherapistHasAgeGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_has_age_group', function (Blueprint $table) {
            $table->integer('therapist_id')->unsigned();
            $table->integer('age_group_id')->unsigned();

            $table->foreign('therapist_id')
                ->references('id')
                ->on('therapist_survey')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');

            $table->foreign('age_group_id')
                ->references('id')
                ->on('therapist_survey_age_groups')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('therapist_has_age_group');
    }
}
