<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientDocumentConsentInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_document_consent_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_document_id')->unsigned()->key();
            $table->boolean('allow_mailing')->nullable();
            $table->boolean('allow_home_phone_call')->nullable();
            $table->boolean('allow_mobile_phone_call')->nullable();
            $table->boolean('allow_mobile_send_messages')->nullable();
            $table->boolean('allow_work_phone_call')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_document_consent_info');
    }
}
