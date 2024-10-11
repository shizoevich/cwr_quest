<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOtherDocumentTypeFieldInPatientDocumentsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('patient_documents', function(Blueprint $table) {
            $table->string('other_document_type')->nullable()->after('document_type_id');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('patient_documents', function(Blueprint $table) {
            $table->dropColumn('other_document_type');
            $table->dropColumn('deleted_at');
        });
    }
}
