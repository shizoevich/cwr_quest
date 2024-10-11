<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTherapistHasFocusFirstTableForeignKeys extends Migration
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

            $table->renameColumn('focus_first_id', 'focus_id');

            $table->foreign('therapist_id')
                ->references('id')
                ->on('therapist_survey')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('focus_id')
                ->references('id')
                ->on('therapist_survey_practice_focus')
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
            $table->dropForeign(['focus_id']);

            $table->renameColumn('focus_id', 'focus_first_id');

            $table->foreign('therapist_id')
                ->references('id')
                ->on('therapist_survey')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

                $table->foreign('focus_first_id')
                ->references('id')
                    ->on('therapist_survey_practice_focus')
                    ->onUpdate('CASCADE')
                    ->onDelete('CASCADE');
            });
    }
}
