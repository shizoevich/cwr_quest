<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBillingPeriodIdFkToSalaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary', function(Blueprint $table) {
            $table->foreign('billing_period_id')
                ->references('id')
                ->on('billing_periods')
                ->onDelete('set null')
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
        Schema::table('salary', function(Blueprint $table) {
            $table->dropForeign(['billing_period_id']);
        });
    }
}
