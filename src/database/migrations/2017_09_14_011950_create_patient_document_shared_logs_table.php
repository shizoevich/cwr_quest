<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientDocumentSharedLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_document_shared_logs', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_document_shared_id')->unsigned()->index();
            $table->integer('patient_document_statuses_id')->unsigned()->index();
            $table->timestamps();

            $table->foreign('patient_document_shared_id', 'pd_shared_id_foreign')
                ->references('id')
                ->on('patient_document_shared')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');

            $table->foreign('patient_document_statuses_id', 'pd_statuses_id_foreign')
                ->references('id')
                ->on('shared_document_statuses')
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
        Schema::dropIfExists('patient_document_shared_logs');
    }
}
