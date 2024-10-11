<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryTimesheetLateCancellationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_timesheet_late_cancellations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('appointment_id')->nullable();
            $table->unsignedInteger('billing_period_id');
            $table->integer('patient_id');
            $table->integer('provider_id');
            $table->date('date');
            $table->unsignedInteger('amount');
            $table->boolean('is_custom_created')->default(false);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
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
        Schema::dropIfExists('salary_timesheet_late_cancellations');
    }
}
