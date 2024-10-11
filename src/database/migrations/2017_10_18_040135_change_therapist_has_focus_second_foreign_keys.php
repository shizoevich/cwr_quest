<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTherapistHasFocusSecondForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_has_focus_second', function (Blueprint $table) {

            $table->dropForeign(['therapist_id']);
            $table->dropForeign(['focus_second_id']);

            $table->foreign('therapist_id')
                ->references('id')
                ->on('therapist_survey')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('focus_second_id')
                ->references('id')
                ->on('therapist_survey_practice_focus_second')
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
        Schema::table('therapist_has_focus_second', function (Blueprint $table) {

            $table->dropForeign(['therapist_id']);
            $table->dropForeign(['focus_second_id']);

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
}
