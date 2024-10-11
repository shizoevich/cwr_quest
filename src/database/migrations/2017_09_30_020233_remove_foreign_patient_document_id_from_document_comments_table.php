<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveForeignPatientDocumentIdFromDocumentCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::table('patient_document_comments', function(Blueprint $table)
        {
            $table->dropForeign(['patient_documents_id']);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_document_comments', function(Blueprint $table) {

            $table->foreign('patient_documents_id')
                ->references('id')
                ->on('patient_documents')
                ->onDelete('cascade')
                ->onUpdate('cascade');

        });
    }
}
