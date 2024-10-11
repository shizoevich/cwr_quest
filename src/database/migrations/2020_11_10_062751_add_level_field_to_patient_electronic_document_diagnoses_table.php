<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLevelFieldToPatientElectronicDocumentDiagnosesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_electronic_document_diagnoses', function(Blueprint $table) {
            $table->unsignedTinyInteger('level')->default(1);
            $table->unique(['patient_electronic_document_id', 'diagnose_id', 'level'], 'patient_electronic_document_diagnose_lvl_unique');
            $table->dropUnique('patient_electronic_document_diagnose_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_electronic_document_diagnoses', function(Blueprint $table) {
            $table->unique(['patient_electronic_document_id', 'diagnose_id'], 'patient_electronic_document_diagnose_unique');
            $table->dropUnique('patient_electronic_document_diagnose_lvl_unique');
            $table->dropColumn('level');
        });
    }
}
