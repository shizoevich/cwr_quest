<?php

use Illuminate\Database\Migrations\Migration;

class SyncProviderTariffPlanIdWithPatientVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        \DB::statement("
            UPDATE `patient_visits`
            JOIN `providers_tariffs_plans` ON `providers_tariffs_plans`.`provider_id` = `patient_visits`.`provider_id`
            SET `patient_visits`.`provider_tariff_plan_id` = `providers_tariffs_plans`.`tariff_plan_id`
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        \DB::statement("
            UPDATE `patient_visits`
            SET `patient_visits`.`provider_tariff_plan_id` = NULL
        ");
    }
}
