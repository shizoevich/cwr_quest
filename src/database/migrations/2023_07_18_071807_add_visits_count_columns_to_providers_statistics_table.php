<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisitsCountColumnsToProvidersStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providers_statistics', function (Blueprint $table) {
            $table->integer('visits_count')->default(0)->after('provider_revenue');
            $table->integer('applied_visits_count')->default(0)->after('visits_count');
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
            $table->dropColumn('visits_count');
            $table->dropColumn('applied_visits_count');
        });
    }
}
