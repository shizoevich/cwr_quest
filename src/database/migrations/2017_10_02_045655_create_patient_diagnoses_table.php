<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientDiagnosesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('patient_diagnoses', function (Blueprint $table) {
            $table->integer('patient_officeally_id');
            $table->text('diagnose');
            $table->timestamps();

            $table->primary('patient_officeally_id');
            $table->foreign('patient_officeally_id')
                ->references('patient_id')
                ->on('patients')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('patient_diagnoses');
    }
}
