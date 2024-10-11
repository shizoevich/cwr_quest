<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocumentTypeIdFieldInPatientDocumentsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('patient_documents', function(Blueprint $table) {
            $table->integer('document_type_id')->unsigned()->nullable();

            $table->foreign('document_type_id')
                ->references('id')
                ->on('patient_document_types')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('patient_documents', function(Blueprint $table) {
            $table->dropForeign('patient_documents_document_type_id_foreign');
            $table->dropColumn('document_type_id');
        });
    }
}
