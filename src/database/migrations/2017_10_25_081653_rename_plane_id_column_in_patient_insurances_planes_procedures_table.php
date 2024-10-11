<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenamePlaneIdColumnInPatientInsurancesPlanesProceduresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_insurances_planes_procedures', function(Blueprint $table) {
            $table->renameColumn('plan_id', 'plane_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('patient_insurances_planes_procedures', 'plane_id')) {
            Schema::table('patient_insurances_planes_procedures', function (Blueprint $table) {
                $table->renameColumn('plane_id', 'plan_id');
            });
        }
    }
}
