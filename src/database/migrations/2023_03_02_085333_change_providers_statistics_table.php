<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeProvidersStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providers_statistics', function(Blueprint $table) {
            $table->float('initial_availability_length')->default(0)->change();
            $table->float('remaining_availability_length')->default(0)->change();
            $table->integer('appointments_count')->default(0)->change();
            $table->float('appointments_length')->default(0)->change();
            $table->integer('kaiser_appointments_count')->default(0)->change();
            $table->integer('active_appointments_count')->default(0)->change();
            $table->float('active_appointments_length')->default(0)->change();
            $table->integer('completed_appointments_count')->default(0)->change();
            $table->float('completed_appointments_length')->default(0)->change();
            $table->integer('visit_created_appointments_count')->default(0)->change();
            $table->float('visit_created_appointments_length')->default(0)->change();
            $table->integer('cancelled_appointments_count')->default(0)->change();
            $table->float('cancelled_appointments_length')->default(0)->change();
            $table->integer('rescheduled_appointments_count')->default(0)->change();
            $table->integer('last_minute_reschedule_appointments_count')->default(0)->change();
            $table->integer('cancelled_by_patient_appointments_count')->default(0)->change();
            $table->integer('cancelled_by_provider_appointments_count')->default(0)->change();
            $table->integer('last_minute_cancel_by_patient_appointments_count')->default(0)->change();
            $table->integer('patient_did_not_come_appointments_count')->default(0)->change();
            $table->integer('cancelled_by_office_appointments_count')->default(0)->change();
            $table->float('cancelled_appointments_rate', 8, 4)->default(0)->change();
            $table->float('total_cancelled_appointments_rate', 8, 4)->default(0)->change();
            $table->float('avg_initial_availability_length')->default(0)->change();
            $table->float('avg_remaining_availability_length')->default(0)->change();
            $table->float('avg_appointments_count')->default(0)->change();
            $table->float('avg_appointments_length')->default(0)->change();
            $table->float('avg_active_appointments_count')->default(0)->change();
            $table->float('avg_active_appointments_length')->default(0)->change();
            $table->float('avg_completed_appointments_count')->default(0)->change();
            $table->float('avg_completed_appointments_length')->default(0)->change();
            $table->float('avg_visit_created_appointments_count')->default(0)->change();
            $table->float('avg_visit_created_appointments_length')->default(0)->change();
            $table->float('avg_cancelled_appointments_count')->default(0)->change();
            $table->float('avg_cancelled_appointments_length')->default(0)->change();
            $table->float('avg_cancelled_appointments_rate', 8, 4)->default(0)->change();
            $table->float('total_avg_cancelled_appointments_rate', 8, 4)->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providers_statistics', function(Blueprint $table) {
            $table->float('initial_availability_length')->change();
            $table->float('remaining_availability_length')->change();
            $table->integer('appointments_count')->change();
            $table->float('appointments_length')->change();
            $table->integer('kaiser_appointments_count')->change();
            $table->integer('active_appointments_count')->change();
            $table->float('active_appointments_length')->change();
            $table->integer('completed_appointments_count')->change();
            $table->float('completed_appointments_length')->change();
            $table->integer('visit_created_appointments_count')->change();
            $table->float('visit_created_appointments_length')->change();
            $table->integer('cancelled_appointments_count')->change();
            $table->float('cancelled_appointments_length')->change();
            $table->integer('rescheduled_appointments_count')->change();
            $table->integer('last_minute_reschedule_appointments_count')->change();
            $table->integer('cancelled_by_patient_appointments_count')->change();
            $table->integer('cancelled_by_provider_appointments_count')->change();
            $table->integer('last_minute_cancel_by_patient_appointments_count')->change();
            $table->integer('patient_did_not_come_appointments_count')->change();
            $table->integer('cancelled_by_office_appointments_count')->change();
            $table->float('cancelled_appointments_rate')->change();
            $table->float('total_cancelled_appointments_rate')->change();
            $table->float('avg_initial_availability_length')->change();
            $table->float('avg_remaining_availability_length')->change();
            $table->float('avg_appointments_count')->change();
            $table->float('avg_appointments_length')->change();
            $table->float('avg_active_appointments_count')->change();
            $table->float('avg_active_appointments_length')->change();
            $table->float('avg_completed_appointments_count')->change();
            $table->float('avg_completed_appointments_length')->change();
            $table->float('avg_visit_created_appointments_count')->change();
            $table->float('avg_visit_created_appointments_length')->change();
            $table->float('avg_cancelled_appointments_count')->change();
            $table->float('avg_cancelled_appointments_length')->change();
            $table->float('avg_cancelled_appointments_rate')->change();
            $table->float('total_avg_cancelled_appointments_rate')->change();
        });
    }
}
