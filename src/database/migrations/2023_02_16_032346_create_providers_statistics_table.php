<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvidersStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers_statistics', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id')->key();
            $table->integer('billing_period_id')->unsigned()->key();
            $table->float('initial_availability_length');
            $table->float('remaining_availability_length');
            $table->integer('appointments_count');
            $table->float('appointments_length');
            $table->integer('kaiser_appointments_count');
            $table->integer('active_appointments_count');
            $table->float('active_appointments_length');
            $table->integer('completed_appointments_count');
            $table->float('completed_appointments_length');
            $table->integer('visit_created_appointments_count');
            $table->float('visit_created_appointments_length');
            $table->integer('cancelled_appointments_count');
            $table->float('cancelled_appointments_length');
            $table->integer('rescheduled_appointments_count');
            $table->integer('last_minute_reschedule_appointments_count');
            $table->integer('cancelled_by_patient_appointments_count');
            $table->integer('cancelled_by_provider_appointments_count');
            $table->integer('last_minute_cancel_by_patient_appointments_count');
            $table->integer('patient_did_not_come_appointments_count');
            $table->integer('cancelled_by_office_appointments_count');
            $table->float('cancelled_appointments_rate');
            $table->float('total_cancelled_appointments_rate');
            $table->float('avg_initial_availability_length');
            $table->float('avg_remaining_availability_length');
            $table->float('avg_appointments_count');
            $table->float('avg_appointments_length');
            $table->float('avg_active_appointments_count');
            $table->float('avg_active_appointments_length');
            $table->float('avg_completed_appointments_count');
            $table->float('avg_completed_appointments_length');
            $table->float('avg_visit_created_appointments_count');
            $table->float('avg_visit_created_appointments_length');
            $table->float('avg_cancelled_appointments_count');
            $table->float('avg_cancelled_appointments_length');
            $table->float('avg_cancelled_appointments_rate');
            $table->float('total_avg_cancelled_appointments_rate');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('providers_statistics');
    }
}
