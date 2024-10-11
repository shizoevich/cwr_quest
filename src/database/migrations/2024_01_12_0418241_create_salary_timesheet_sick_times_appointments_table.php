<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryTimesheetSickTimesAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_timesheet_sick_times_appointments', function (Blueprint $table) {
            $table->unsignedInteger('sick_time_id');
            $table->integer('appointment_id');

            $table->foreign('sick_time_id')
                ->references('id')
                ->on('salary_timesheet_sick_times')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
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
        Schema::dropIfExists('salary_timesheet_sick_times_appointments');
    }
}
