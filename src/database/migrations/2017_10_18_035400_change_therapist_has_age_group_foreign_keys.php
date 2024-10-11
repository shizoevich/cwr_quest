<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTherapistHasAgeGroupForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_has_age_group', function (Blueprint $table) {

            $table->dropForeign(['therapist_id']);
            $table->dropForeign(['age_group_id']);

            $table->foreign('therapist_id')
                ->references('id')
                ->on('therapist_survey')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('age_group_id')
                ->references('id')
                ->on('therapist_survey_age_groups')
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
        Schema::table('therapist_has_age_group', function (Blueprint $table) {

            $table->dropForeign(['therapist_id']);
            $table->dropForeign(['age_group_id']);

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
}
