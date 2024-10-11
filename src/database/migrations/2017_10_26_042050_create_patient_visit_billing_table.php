<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientVisitBillingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_visit_billings', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('visit_id')->unsigned();
            $table->integer('pos');
            $table->integer('cpt');
            $table->integer('insurance_procedure_id')->unsigned();
            $table->timestamps();

            $table->foreign('visit_id')
                ->references('id')
                ->on('patient_visits')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');

            $table->foreign('insurance_procedure_id')
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
        Schema::dropIfExists('patient_visit_billings');
    }
}
