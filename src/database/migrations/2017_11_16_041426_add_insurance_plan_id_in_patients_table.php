<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInsurancePlanIdInPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function(Blueprint $table) {
            $table->integer('insurance_plan_id')->unsigned()->nullable()->after('primary_insurance_id');

            $table->foreign('insurance_plan_id')
                ->references('id')
                ->on('patient_insurances_plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients', function(Blueprint $table) {
            $table->dropForeign(['insurance_plan_id']);
            $table->dropColumn('insurance_plan_id');
        });
    }
}
