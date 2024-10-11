<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExternalIdFieldToPatientInsurancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_insurances', function(Blueprint $table) {
            $table->string('external_id', 32)->nullable()->unique()->after('id');
        });
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_insurances', function(Blueprint $table) {
            $table->dropColumn('external_id');
        });
    }
}
