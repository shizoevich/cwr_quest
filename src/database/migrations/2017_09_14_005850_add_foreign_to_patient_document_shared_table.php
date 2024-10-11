<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignToPatientDocumentSharedTable extends Migration
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
            $table->integer('shared_document_methods_id')->unsigned()->after('patient_documents_id');
            $table->foreign('shared_document_methods_id')
                ->references('id')
                ->on('shared_document_methods')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_document_shared', function(Blueprint $table)
        {
            $table->dropForeign(['shared_document_methods_id']);
        });
    }
}
