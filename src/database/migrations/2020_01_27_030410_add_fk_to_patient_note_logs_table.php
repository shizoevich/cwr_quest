<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToPatientNoteLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_note_logs', function (Blueprint $table) {
            $table->foreign('patient_note_id')
                ->references('id')
                ->on('patient_notes')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_note_logs', function (Blueprint $table) {
            $table->dropForeign(['patient_note_id']);
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['provider_id']);
            $table->dropForeign(['user_id']);
        });
    }
}
