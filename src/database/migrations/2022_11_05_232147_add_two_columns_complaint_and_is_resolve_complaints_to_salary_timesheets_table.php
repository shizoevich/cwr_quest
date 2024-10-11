<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTwoColumnsComplaintAndIsResolveComplaintsToSalaryTimesheetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_timesheets', function (Blueprint $table) {
            $table->longText('complaint')->nullable()->after('completed_timesheet');
        });

        Schema::table('salary_timesheets', function (Blueprint $table) {
            $table->boolean('is_resolve_complaint')->nullable()->after('complaint');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_timesheets', function (Blueprint $table) {
            $table->dropColumn('complaint');
            $table->dropColumn('is_resolve_complaint');
        });
    }
}
