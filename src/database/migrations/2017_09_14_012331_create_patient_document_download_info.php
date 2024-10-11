<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientDocumentDownloadInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_document_download_info', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_document_shared_id')->unsigned()->index();
            $table->string('client_ip');
            $table->string('client_user_agent');
            $table->timestamps();

            $table->foreign('patient_document_shared_id', 'pds_id_foreign')
                ->references('id')
                ->on('patient_document_shared')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_document_download_info');
    }
}
