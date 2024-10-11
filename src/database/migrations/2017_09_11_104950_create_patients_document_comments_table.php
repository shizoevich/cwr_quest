<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePatientsDocumentCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_document_comments',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('patient_documents_id')->unsigned()->index();
                $table->integer('provider_id')->index();
                $table->string('content');
                $table->timestamps();

                $table->foreign('patient_documents_id')
                    ->references('id')
                    ->on('patient_documents')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                $table->foreign('provider_id')
                    ->references('id')
                    ->on('providers')
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
        Schema::dropIfExists('patient_document_comments');
    }
}
