<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDocumentTypeFieldInPatientDocumentRequestItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_document_request_items', function(Blueprint $table) {
            $table->renameColumn('document_type', 'form_type_id');
        });
        Schema::table('patient_document_request_items', function(Blueprint $table) {
            $table->foreign('form_type_id')
                ->references('id')
                ->on('patient_form_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_document_request_items', function(Blueprint $table) {
            $table->dropForeign(['form_type_id']);
        });
        Schema::table('patient_document_request_items', function(Blueprint $table) {
            $table->renameColumn('form_type_id', 'document_type');
        });
    }
}
