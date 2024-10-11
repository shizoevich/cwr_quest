<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInsuranceIdToPatientVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_visits', function(Blueprint $table) {
            $table->integer('insurance_id')->nullable()->unsigned()->after('provider_id');
            $table->integer('plane_id')->nullable()->unsigned()->after('insurance_id');

            $table->foreign('insurance_id')
                ->references('id')
                ->on('patient_insurances')
                ->onDelete('NO ACTION')
                ->onUpdate('NO ACTION');


            $table->foreign('plane_id')
                ->references('id')
                ->on('patient_insurances_planes')
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
        Schema::table('patient_visits', function(Blueprint $table) {
            $table->dropForeign(['insurance_id']);
            $table->dropColumn(['insurance_id']);
        });
    }
}
