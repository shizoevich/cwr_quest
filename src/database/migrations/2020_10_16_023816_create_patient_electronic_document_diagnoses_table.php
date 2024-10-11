<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientElectronicDocumentDiagnosesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_electronic_document_diagnoses', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('patient_electronic_document_id');
            $table->unsignedInteger('diagnose_id');
            $table->unique(['patient_electronic_document_id', 'diagnose_id'], 'patient_electronic_document_diagnose_unique');
            
            $table->foreign('patient_electronic_document_id', 'document_diagnoses_patient_electronic_document_id_foreign')
                ->references('id')
                ->on('patient_electronic_documents')
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
        Schema::dropIfExists('patient_electronic_document_diagnoses');
    }
}
