<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToSalaryTimesheets2Table extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary_timesheets', function(Blueprint $table) {
            $table->foreign('billing_period_id')
                ->references('id')
                ->on('billing_periods')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
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
        Schema::table('salary_timesheets', function(Blueprint $table) {
            $table->dropForeign(['billing_period_id']);
            $table->dropForeign(['provider_id']);
        });
    }
}
