<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientDocumentSharedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_document_shared', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_documents_id')->unsigned()->index();
            $table->string('recipient');
            $table->string('shared_link', 191)->unique('shared_link');
            $table->string('shared_link_password');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('patient_documents_id')
                ->references('id')
                ->on('patient_documents')
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
        Schema::dropIfExists('patient_document_type_default_addresses');
        Schema::dropIfExists('patient_document_download_info');
        Schema::dropIfExists('patient_document_shared_logs');
        Schema::dropIfExists('patient_document_shared');
    }
}
