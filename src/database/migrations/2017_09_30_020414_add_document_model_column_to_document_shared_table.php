<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDocumentModelColumnToDocumentSharedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('patient_document_shared', function(Blueprint $table)
        {
            $table->string('document_model')->after('patient_documents_id');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_document_shared', function(Blueprint $table) {

            $table->dropColumn(['document_model']);

        });
    }
}
