<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkRequestItemToPatientDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_documents', function (Blueprint $table) {
            $table->foreign('document_request_item_id')->references('id')->on('patient_document_request_items');
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
            $table->dropForeign(['document_request_item_id']);
        });
    }
}
