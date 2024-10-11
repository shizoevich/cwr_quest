<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenamePlanesToPlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_insurances_planes_procedures', function(Blueprint $table) {
            $table->renameColumn('plane_id', 'plan_id');
        });
        Schema::rename('patient_insurances_planes_procedures', 'patient_insurances_plans_procedures');
        Schema::rename('patient_insurances_planes', 'patient_insurances_plans');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('patient_insurances_planes_procedures', 'plan_id')) {
            Schema::table('patient_insurances_planes_procedures', function (Blueprint $table) {
                $table->renameColumn('plan_id', 'plane_id');
            });
        }
    
        Schema::rename('patient_insurances_plans_procedures', 'patient_insurances_planes_procedures');
        Schema::rename('patient_insurances_plans', 'patient_insurances_planes');
    }
}
