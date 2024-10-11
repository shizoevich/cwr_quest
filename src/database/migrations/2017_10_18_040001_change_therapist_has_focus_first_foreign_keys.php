<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTherapistHasFocusFirstForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_has_focus_first', function (Blueprint $table) {


            $table->dropForeign(['therapist_id']);
            $table->dropForeign(['focus_first_id']);

            $table->foreign('therapist_id')
                ->references('id')
                ->on('therapist_survey')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('focus_first_id')
                ->references('id')
                ->on('therapist_survey_practice_focus_first')
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
        Schema::table('therapist_has_focus_first', function (Blueprint $table) {

            $table->dropForeign(['therapist_id']);
            $table->dropForeign(['focus_first_id']);

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
}
