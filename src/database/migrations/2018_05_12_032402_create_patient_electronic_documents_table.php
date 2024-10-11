<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientElectronicDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_electronic_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('document_type_id');
            $table->integer('provider_id');
            $table->integer('patient_id');
            $table->longText('document_data');
            $table->timestamp('start_editing_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_electronic_documents');
    }
}
