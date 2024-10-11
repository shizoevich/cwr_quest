<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAddressFieldsToPatientInsurancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_insurances', function(Blueprint $table) {
            $table->string('address_line_1')->after('insurance')->nullable();
            $table->string('city', 64)->after('address_line_1')->nullable();
            $table->string('state', 16)->after('city')->nullable();
            $table->string('zip', 16)->after('state')->nullable();
            $table->dropUnique(['insurance']);
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
            $table->dropColumn([
                'address_line_1',
                'city',
                'state',
                'zip',
            ]);
            $table->unique('insurance');
        });
    }
}
