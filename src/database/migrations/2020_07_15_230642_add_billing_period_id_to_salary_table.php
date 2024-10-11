<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBillingPeriodIdToSalaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary', function(Blueprint $table) {
            $table->unsignedInteger('billing_period_id')->nullable()->after('paid_fee');
        });
    }

    /**2020_07_15_230827_add_billing_period_id_fk_to_salary_table
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary', function(Blueprint $table) {
            $table->dropColumn('billing_period_id');
        });
    }
}
