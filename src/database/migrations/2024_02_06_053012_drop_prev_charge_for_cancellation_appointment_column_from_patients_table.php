<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropPrevChargeForCancellationAppointmentColumnFromPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('prev_charge_for_cancellation_appointment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->integer('prev_charge_for_cancellation_appointment')->default(0)->after('charge_for_cancellation_appointment')->comment("supporting column, not used in the system. Stores previous value of 'charge_for_cancellation_appointment'");
        });
    }
}
