<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientLeadDiagnosesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_lead_diagnoses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('patient_lead_id');
            $table->unsignedInteger('diagnose_id');
            $table->timestamps();

            $table->unique(['patient_lead_id', 'diagnose_id']);

            $table->foreign('patient_lead_id')
                ->references('id')
                ->on('patient_leads')
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
        Schema::dropIfExists('patient_lead_diagnoses');
    }
}
