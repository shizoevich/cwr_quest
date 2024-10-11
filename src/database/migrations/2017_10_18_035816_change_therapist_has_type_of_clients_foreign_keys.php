<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTherapistHasTypeOfClientsForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_has_client_type', function (Blueprint $table) {

            $table->dropForeign(['therapist_id']);
            $table->dropForeign(['client_type_id']);

            $table->foreign('therapist_id')
                ->references('id')
                ->on('therapist_survey')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('client_type_id')
                ->references('id')
                ->on('therapist_survey_type_of_clients')
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
        Schema::table('therapist_has_client_type', function (Blueprint $table) {

            $table->dropForeign(['therapist_id']);
            $table->dropForeign(['client_type_id']);

            $table->foreign('therapist_id')
                ->references('id')
                ->on('therapist_survey')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');

            $table->foreign('client_type_id')
                ->references('id')
                ->on('therapist_survey_type_of_clients')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');
        });
    }
}
