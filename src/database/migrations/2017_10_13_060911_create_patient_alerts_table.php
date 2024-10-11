<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_alerts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->index();
            $table->integer('officeally_alert_id')->unique();
            $table->date('date_created');
            $table->string('message');
            $table->string('resolved_by')->nullable();
            $table->date('date_resolved')->nullable();
            $table->string('status');
            $table->timestamps();

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_alerts');
    }
}
