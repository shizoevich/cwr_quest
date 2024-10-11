<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTherapistHasFocusSecondTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_has_focus_second', function (Blueprint $table) {
            $table->integer('therapist_id')->unsigned();
            $table->integer('focus_second_id')->unsigned();

            $table->foreign('therapist_id')
                ->references('id')
                ->on('therapist_survey')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');

            $table->foreign('focus_second_id')
                ->references('id')
                ->on('therapist_survey_practice_focus_second')
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
        Schema::dropIfExists('therapist_has_focus_second');
    }
}
