<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPatientInsurancesProcedureIdToTamplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_templates', function(Blueprint $table) {
            $table->unsignedInteger('patient_insurances_procedure_id')
                ->nullable()
                ->after('pos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_templates', function(Blueprint $table) {
            $table->dropColumn('patient_insurances_procedure_id');
        });
    }
}
