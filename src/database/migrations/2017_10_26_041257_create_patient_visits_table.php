<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_visits', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('visit_id')->unique();
            $table->integer('appointment_id')->nullable();
            $table->integer('patient_id')->nullable();
            $table->integer('provider_id')->nullable();
            $table->tinyInteger('is_paid')->default(0);
            $table->date('date')->notNull();
            $table->timestamps();

            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
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
        Schema::dropIfExists('patient_visits');
    }
}
