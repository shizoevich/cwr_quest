<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPatientsWithVisitsCountColumnToProvidersStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providers_statistics', function (Blueprint $table) {
            $table->integer('patients_count')->default(0)->after('total_avg_cancelled_appointments_rate');
            $table->integer('patients_with_visits_count')->default(0)->after('patients_count');
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
            $table->dropColumn('patients_count');
            $table->dropColumn('patients_with_visits_count');
        });
    }
}
