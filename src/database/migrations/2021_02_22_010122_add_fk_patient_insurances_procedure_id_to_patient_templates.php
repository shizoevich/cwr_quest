<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkPatientInsurancesProcedureIdToPatientTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_templates', function (Blueprint $table) {
            $table->foreign('patient_insurances_procedure_id')
                ->references('id')
                ->on('patient_insurances_procedures')
                ->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_templates', function (Blueprint $table) {
            $table->dropForeign(['patient_insurances_procedure_id']);
        });
    }
}
