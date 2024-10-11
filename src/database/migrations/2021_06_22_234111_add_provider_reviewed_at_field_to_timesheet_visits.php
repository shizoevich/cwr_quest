<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProviderReviewedAtFieldToTimesheetVisits extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_timesheet_visits', function(Blueprint $table) {
            $table->timestamp('provider_reviewed_at')->nullable()->after('is_custom_created');
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
            $table->dropColumn('provider_reviewed_at');
        });
    }
}
