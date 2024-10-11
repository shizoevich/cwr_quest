<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCommentColumnToSalaryTimesheetSupervisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_timesheet_supervisions', function (Blueprint $table) {
            $table->text('comment')->after('supervision_hours')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary_timesheet_supervisions', function (Blueprint $table) {
            $table->dropColumn('comment');
        });
    }
}
