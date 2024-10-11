<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToPatientInsuranceProceduresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_insurances_procedures', function(Blueprint $table) {
            $table->string('pos')->nullable()->after('name');
            $table->string('modifier_a')->nullable()->after('pos');
            $table->string('modifier_b')->nullable()->after('modifier_a');
            $table->string('modifier_c')->nullable()->after('modifier_b');
            $table->string('modifier_d')->nullable()->after('modifier_c');
            $table->float('charge')->nullable()->after('modifier_d');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_insurances_procedures', function(Blueprint $table) {
            $table->dropColumn([
                'pos',
                'modifier_a',
                'modifier_b',
                'modifier_c',
                'modifier_d',
                'charge',
            ]);
        });
    }
}
