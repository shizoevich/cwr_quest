<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryTimesheetSupervisionHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_timesheet_supervision_hours', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('billing_period_id');
            $table->integer('provider_id')->key();
            $table->integer('supervisor_id')->key();
            $table->float('supervision_hours', 4, 2)->default(0);
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
        Schema::dropIfExists('salary_timesheet_supervision_hours');
    }
}
