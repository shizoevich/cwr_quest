<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientNoteDiagnosesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_note_diagnoses', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_note_id');
            $table->unsignedInteger('diagnose_id');
            $table->unique(['patient_note_id', 'diagnose_id']);
            
            $table->foreign('patient_note_id')
                ->references('id')
                ->on('patient_notes')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('diagnose_id')
                ->references('id')
                ->on('diagnoses')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_note_diagnoses');
    }
}
