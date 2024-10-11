<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsTelehelthFieldToSalaryTimesheetVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_timesheet_visits', function(Blueprint $table) {
            $table->boolean('is_telehealth')->default(false)->after('is_overtime');
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
            $table->dropColumn('is_telehealth');
        });
    }
}
