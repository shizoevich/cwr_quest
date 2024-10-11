<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientDocumentRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_document_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id');
            $table->unsignedInteger('sent_by');
            $table->string('hash', 32)->unique();
            $table->string('sent_to_email', 191);
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
        Schema::dropIfExists('patient_document_requests');
    }
}
