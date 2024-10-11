<?php

use App\PatientInsurancePlanProcedure;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangePriceFieldsInPatientInsurancesPlansProceduresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        PatientInsurancePlanProcedure::query()->update([
            'price' => \DB::raw('price * 100'),
            'telehealth_price' => \DB::raw('telehealth_price * 100'),
        ]);
        Schema::table('patient_insurances_plans_procedures', function(Blueprint $table) {
            $table->integer('price')->nullable()->change()->comment('Divide by 100 for get original value');
            $table->integer('telehealth_price')->nullable()->change()->comment('Divide by 100 for get original value');
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
            $table->float('price')->nullable()->change();
            $table->float('telehealth_price')->nullable()->change();
        });
        PatientInsurancePlanProcedure::query()->update([
            'price' => \DB::raw('price / 100'),
            'telehealth_price' => \DB::raw('telehealth_price / 100'),
        ]);
    }
}
