<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReviewedAtFieldToSalaryTimesheetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_timesheets', function(Blueprint $table) {
            $table->timestamp('reviewed_at')->after('completed_timesheet')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_timesheets', function(Blueprint $table) {
            $table->dropColumn('reviewed_at');
        });
    }
}
