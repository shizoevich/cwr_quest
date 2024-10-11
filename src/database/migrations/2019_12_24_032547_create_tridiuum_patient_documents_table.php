<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTridiuumPatientDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tridiuum_patient_documents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('external_id', 191)->unique();
            $table->integer('internal_id')->unsigned()->key()->nullable();
            $table->integer('tridiuum_patient_id')->unsigned()->key();
            $table->integer('type_id')->unsigned()->key();
            $table->boolean('is_downloaded');
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
        Schema::drop('tridiuum_patient_documents');
    }
}
