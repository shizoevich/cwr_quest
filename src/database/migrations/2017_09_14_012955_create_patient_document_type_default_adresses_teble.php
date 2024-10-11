<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientDocumentTypeDefaultAdressesTeble extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_document_type_default_addresses', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_document_types_id')->unsigned()->index('pdt_index');
            $table->string('email');
            $table->string('fax');

            $table->foreign('patient_document_types_id', 'pdt_id_foreign')
                ->references('id')
                ->on('patient_document_types')
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
    }
}
