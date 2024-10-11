<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToTridiuumPatientDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tridiuum_patient_documents', function (Blueprint $table) {
            $table->foreign('internal_id')
                ->references('id')
                ->on('patient_documents')
                ->onDelete('SET NULL')
                ->onUpdate('CASCADE');
            $table->foreign('tridiuum_patient_id')
                ->references('id')
                ->on('tridiuum_patients')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('type_id')
                ->references('id')
                ->on('tridiuum_patient_document_types')
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
        Schema::table('tridiuum_patient_documents', function (Blueprint $table) {
            $table->dropForeign(['internal_id']);
            $table->dropForeign(['tridiuum_patient_id']);
            $table->dropForeign(['type_id']);
        });
    }
}
