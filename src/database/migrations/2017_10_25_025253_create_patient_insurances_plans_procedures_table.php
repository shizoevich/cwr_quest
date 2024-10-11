<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientInsurancesPlansProceduresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_insurances_planes_procedures', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('plan_id')->unsigned();
            $table->integer('procedure_id')->unsigned();
            $table->float('price');
            $table->timestamps();

            $table->foreign('plan_id')
                ->references('id')
                ->on('patient_insurances_planes')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');
            $table->foreign('procedure_id')
                ->references('id')
                ->on('patient_insurances_procedures')
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
        Schema::dropIfExists('patient_insurances_planes_procedures');
    }
}
