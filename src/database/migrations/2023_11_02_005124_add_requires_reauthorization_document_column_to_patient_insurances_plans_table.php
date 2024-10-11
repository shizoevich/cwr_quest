<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequiresReauthorizationDocumentColumnToPatientInsurancesPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_insurances_plans', function (Blueprint $table) {
            $table->boolean('requires_reauthorization_document')->default(true)->after('is_verification_required');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_insurances_plans', function (Blueprint $table) {
            $table->dropColumn(['requires_reauthorization_document']);
        });
    }
}
