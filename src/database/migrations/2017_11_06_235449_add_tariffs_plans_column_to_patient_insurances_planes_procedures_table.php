<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTariffsPlansColumnToPatientInsurancesPlanesProceduresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_insurances_planes_procedures', function(Blueprint $table) {
            $table->integer('tariff_plan_id')->nullable()->unsigned()->after('id');

            $table->foreign('tariff_plan_id')
                ->references('id')
                ->on('tariffs_planes')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_insurances_planes_procedures', function(Blueprint $table) {
            $table->dropForeign(['tariff_plan_id']);
            $table->dropColumn(['tariff_plan_id']);
        });
    }
}
