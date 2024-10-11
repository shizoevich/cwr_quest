<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRevenueColumnsToProvidersStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providers_statistics', function (Blueprint $table) {
            $table->integer('total_revenue')->default(0)->after('transferred_patients_count');
            $table->integer('provider_revenue')->default(0)->after('total_revenue');
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
            $table->dropColumn('total_revenue');
            $table->dropColumn('provider_revenue');
        });
    }
}
