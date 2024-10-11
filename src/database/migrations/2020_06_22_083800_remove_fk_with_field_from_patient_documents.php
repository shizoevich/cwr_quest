<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveFkWithFieldFromPatientDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_documents', function (Blueprint $table) {
            $table->dropForeign(['document_request_id']);
            $table->dropColumn('document_request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_documents', function (Blueprint $table) {
            $table->unsignedInteger('document_request_id')->nullable(true)->default(null)->after('document_type_id');
            $table->foreign('document_request_id')
                ->references('id')
                ->on('patient_document_requests');
        });
    }
}
