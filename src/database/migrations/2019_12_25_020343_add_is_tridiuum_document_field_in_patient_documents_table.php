<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsTridiuumDocumentFieldInPatientDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('patient_documents', function (Blueprint $table) {
            $table->boolean('is_tridiuum_document')->default(0)->after('aws_document_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('patient_documents', function (Blueprint $table) {
            $table->dropColumn('is_tridiuum_document');
        });
    }
}
