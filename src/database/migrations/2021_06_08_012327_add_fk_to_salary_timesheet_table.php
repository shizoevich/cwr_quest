<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToSalaryTimesheetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_timesheet_visits', function(Blueprint $table) {
            $table->foreign('visit_id')
                ->references('id')
                ->on('patient_visits')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('billing_period_id')
                ->references('id')
                ->on('billing_periods')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
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
        Schema::table('salary_timesheet_visits', function(Blueprint $table) {
            $table->dropForeign(['visit_id']);
            $table->dropForeign(['provider_id']);
            $table->dropForeign(['billing_period_id']);
            $table->dropForeign(['patient_id']);
        });
    }
}
