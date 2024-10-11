<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientLeadDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_lead_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_lead_id')->unsigned();
            $table->string('original_document_name', 255);
            $table->string('aws_document_name', 255);
            $table->integer('document_type_id')->unsigned()->nullable();
            $table->boolean('is_tridiuum_document')->default(0);
            $table->boolean('google_drive')->default(0);
            $table->string('other_document_type', 255)->nullable();
            $table->boolean('visible')->default(0);
            $table->boolean('only_for_admin')->default(0);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('patient_lead_id')
                ->references('id')
                ->on('patient_leads')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('document_type_id')
                ->references('id')
                ->on('patient_document_types')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_lead_documents');
    }
}
