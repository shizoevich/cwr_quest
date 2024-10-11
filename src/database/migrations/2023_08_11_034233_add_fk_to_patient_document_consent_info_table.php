<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToPatientDocumentConsentInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_document_consent_info', function (Blueprint $table) {
            $table->foreign('patient_document_id')
                ->references('id')
                ->on('patient_documents')
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
        Schema::table('patient_document_consent_info', function (Blueprint $table) {
            $table->dropForeign(['patient_document_id']);
        });
    }
}
