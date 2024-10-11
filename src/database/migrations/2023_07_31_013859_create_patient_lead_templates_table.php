<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientLeadTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_lead_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('patient_lead_id');
            $table->unsignedInteger('position');
            $table->string('pos')->nullable();
            $table->unsignedInteger('patient_insurances_procedure_id')->nullable();
            $table->string('cpt')->nullable();
            $table->string('modifier_a')->nullable();
            $table->string('modifier_b')->nullable();
            $table->string('modifier_c')->nullable();
            $table->string('modifier_d')->nullable();
            $table->string('diagnose_pointer')->nullable();
            $table->float('charge')->nullable();
            $table->integer('days_or_units')->nullable();
            $table->timestamps();

            $table->unique(['patient_lead_id', 'position']);

            $table->foreign('patient_lead_id')
                ->references('id')
                ->on('patient_leads')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('patient_insurances_procedure_id')
                ->references('id')
                ->on('patient_insurances_procedures')
                ->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_lead_templates');
    }
}
