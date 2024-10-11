<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalaryTimesheetNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('salary_timesheet_notifications', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('billing_period_id');
            $table->integer('provider_id');
            $table->datetime('viewed_at')->nullable();
            $table->datetime('remind_after')->nullable();
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
        Schema::dropIfExists('salary_timesheet_notifications');
    }
}
