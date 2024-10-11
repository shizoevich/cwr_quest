<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTherapistHasFocusFirstTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_has_focus_first', function (Blueprint $table) {
            $table->integer('therapist_id')->unsigned();
            $table->integer('focus_first_id')->unsigned();

            $table->foreign('therapist_id')
                ->references('id')
                ->on('therapist_survey')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');

            $table->foreign('focus_first_id')
                ->references('id')
                ->on('therapist_survey_practice_focus_first')
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
        Schema::dropIfExists('therapist_has_focus_first');
    }
}
