<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTherapistHasInsurancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('therapist_has_insurances', function(Blueprint $table)
        {
            $table->unsignedInteger('therapist_id');
            $table->unsignedInteger('insurance_id');

            $table->foreign('therapist_id')
                ->references('id')
                ->on('therapist_survey')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('insurance_id')
                ->references('id')
                ->on('patient_insurances')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('therapist_has_insurances');
    }
}
