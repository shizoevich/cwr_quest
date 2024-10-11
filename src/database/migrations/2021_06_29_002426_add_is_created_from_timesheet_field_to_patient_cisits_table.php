<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsCreatedFromTimesheetFieldToPatientCisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_visits', function(Blueprint $table) {
            $table->integer('visit_id')->nullable()->change();
            $table->unsignedInteger('salary_timesheet_visit_id')->nullable()->after('reason_id');
            $table->foreign('salary_timesheet_visit_id')
                ->references('id')
                ->on('salary_timesheet_visits')
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
        Schema::table('patient_visits', function(Blueprint $table) {
            $table->integer('visit_id')->change();
            $table->dropForeign(['salary_timesheet_visit_id']);
            $table->dropColumn('salary_timesheet_visit_id');
        });
    }
}
