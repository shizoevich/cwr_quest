<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryTimesheets2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_timesheets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('billing_period_id');
            $table->integer('provider_id');
            $table->float('seek_time');
            $table->boolean('monthly_meeting_attended');
            $table->boolean('changed_appointment_statuses');
            $table->boolean('completed_ia_and_pn');
            $table->boolean('set_diagnoses');
            $table->boolean('completed_timesheet');
            $table->timestamps();
            
            $table->unique(['billing_period_id', 'provider_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('salary_timesheets');
    }
}
