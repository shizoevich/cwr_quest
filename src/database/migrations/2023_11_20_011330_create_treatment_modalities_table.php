<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTreatmentModalitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('treatment_modalities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('insurance_procedure_id')->unsigned();
            $table->string('name');
            $table->boolean('is_telehealth')->default(false);
            $table->tinyInteger('duration');
            $table->tinyInteger('min_duration')->nullable();
            $table->tinyInteger('max_duration')->nullable();
            $table->timestamps();

            $table->foreign('insurance_procedure_id')
                ->references('id')
                ->on('patient_insurances_procedures')
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
        Schema::dropIfExists('treatment_modalities');
    }
}
