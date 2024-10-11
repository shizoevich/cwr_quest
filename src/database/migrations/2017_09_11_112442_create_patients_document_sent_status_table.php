<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientsDocumentSentStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_document_sent_status', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_documents_id')->unsigned()->index();
            $table->tinyInteger('status')->unsigned();
            $table->timestamps();

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
        Schema::dropIfExists('patient_document_sent_status');
    }
}
