<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameProviderTariffPlanesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('providers_tariffs_planes', 'providers_tariffs_plans');
        Schema::table('providers_tariffs_plans', function(Blueprint $table) {
            $table->renameColumn('tariff_plane_id', 'tariff_plan_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('providers_tariffs_plans', 'providers_tariffs_planes');
        Schema::table('providers_tariffs_planes', function(Blueprint $table) {
            $table->renameColumn('tariff_plan_id', 'tariff_plane_id');
        });
    }
}
