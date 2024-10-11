<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPrimaryInsuranceIdInPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function(Blueprint $table) {
            $table->integer('primary_insurance_id')->unsigned()->nullable()->after('primary_insurance');

            $table->foreign('primary_insurance_id')
                ->references('id')
                ->on('patient_insurances');
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
            $table->dropForeign(['primary_insurance_id']);
            $table->dropColumn('primary_insurance_id');
        });
    }
}
