<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixProviderTariffPlanesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \Illuminate\Support\Facades\DB::statement('
            ALTER TABLE `providers_tariffs_plans`
              DROP FOREIGN KEY `providers_tariffs_planes_provider_id_foreign`,
              DROP FOREIGN KEY `providers_tariffs_planes_tariff_plane_id_foreign`;
        ');
        \Illuminate\Support\Facades\DB::statement('
            ALTER TABLE `providers_tariffs_plans`
              ADD CONSTRAINT `providers_tariffs_planes_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE,
              ADD CONSTRAINT `providers_tariffs_planes_tariff_plane_id_foreign` FOREIGN KEY (`tariff_plan_id`) REFERENCES `tariffs_plans` (`id`) ON DELETE CASCADE;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
