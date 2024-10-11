<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCopayFieldToPatientInsurancesPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_insurances_plans', function(Blueprint $table) {
            $table->boolean('need_collect_copay_for_telehealth')->default(false)->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_insurances_plans', function(Blueprint $table) {
            $table->dropColumn('need_collect_copay_for_telehealth');
        });
    }
}
