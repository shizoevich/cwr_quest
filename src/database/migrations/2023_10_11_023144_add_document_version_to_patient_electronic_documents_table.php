<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocumentVersionToPatientElectronicDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_electronic_documents', function (Blueprint $table) {
            $table->float('document_version')->default(1)->after('start_editing_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_electronic_documents', function (Blueprint $table) {
            $table->dropColumn('document_version');
        });
    }
}
