<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_templates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id');
            $table->unsignedInteger('position');
            $table->string('pos')->nullable();
            $table->string('cpt')->nullable();
            $table->string('modifier_a')->nullable();
            $table->string('modifier_b')->nullable();
            $table->string('modifier_c')->nullable();
            $table->string('modifier_d')->nullable();
            $table->string('diagnose_pointer')->nullable();
            $table->float('charge')->nullable();
            $table->integer('days_or_units')->nullable();
            $table->timestamps();
            
            $table->unique(['patient_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_templates');
    }
}
