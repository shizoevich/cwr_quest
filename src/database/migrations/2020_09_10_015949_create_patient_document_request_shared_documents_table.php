<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientDocumentRequestSharedDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_document_request_shared_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('document_request_id');
            $table->string('email', 50);
            $table->string('hash', 32)->unique();
            $table->string('password', 255);
            $table->timestamp('expiring_at')->nullable();
            $table->timestamps();
            
            $table->foreign('document_request_id', 'pat_doc_req_shared_document_request_id_fk')
                ->references('id')
                ->on('patient_document_requests')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_document_request_shared_documents');
    }
}
