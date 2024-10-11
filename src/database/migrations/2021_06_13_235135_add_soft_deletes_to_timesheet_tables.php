<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSoftDeletesToTimesheetTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_timesheet_visits', function(Blueprint $table) {
            $table->softDeletes();
        });
        Schema::table('salary_timesheet_late_cancellations', function(Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_timesheet_visits', function(Blueprint $table) {
            $table->dropSoftDeletes();
        });
        Schema::table('salary_timesheet_late_cancellations', function(Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
}
