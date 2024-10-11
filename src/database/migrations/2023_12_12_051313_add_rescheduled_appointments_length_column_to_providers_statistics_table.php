<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRescheduledAppointmentsLengthColumnToProvidersStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providers_statistics', function (Blueprint $table) {
            $table->float('rescheduled_appointments_length')->default(0)->after('rescheduled_appointments_count');
            $table->dropColumn('last_minute_reschedule_appointments_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providers_statistics', function (Blueprint $table) {
            $table->dropColumn('rescheduled_appointments_length');
            $table->integer('last_minute_reschedule_appointments_count')->default(0)->after('rescheduled_appointments_count');
        });
    }
}
