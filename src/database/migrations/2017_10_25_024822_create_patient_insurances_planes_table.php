<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientInsurancesPlanesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_insurances_planes', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('insurance_id')->unsigned();
            $table->string('name');
            $table->timestamps();

            $table->foreign('insurance_id')
                ->references('id')
                ->on('patient_insurances')
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
        Schema::table('patient_insurances_planes', function (Blueprint $table){

            $table->dropForeign(['insurance_id']);

        });

        Schema::dropIfExists('patient_insurances_planes');
    }
}
