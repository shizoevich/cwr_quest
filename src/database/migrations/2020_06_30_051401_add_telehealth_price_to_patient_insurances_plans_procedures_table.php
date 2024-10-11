<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTelehealthPriceToPatientInsurancesPlansProceduresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_insurances_plans_procedures', function(Blueprint $table) {
            $table->float('telehealth_price')->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_insurances_plans_procedures', function(Blueprint $table) {
            $table->dropColumn('telehealth_price');
        });
    }
}
