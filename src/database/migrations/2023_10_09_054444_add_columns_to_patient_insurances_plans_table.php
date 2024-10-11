<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToPatientInsurancesPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_insurances_plans', function (Blueprint $table) {
            $table->tinyInteger('reauthorization_notification_visits_count')->default(config('app.upcoming_reauthorization_requests_min_visits_count'))->after('is_verification_required')->comment('Remaining visits number from which you need to warn about an upcoming authorization request');
            $table->tinyInteger('reauthorization_notification_days_count')->default(config('app.upcoming_reauthorization_requests_min_days_count'))->after('reauthorization_notification_visits_count')->comment('Remaining days number from which you need to warn about an upcoming authorization request');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_insurances_plans', function (Blueprint $table) {
            $table->dropColumn(['reauthorization_notification_visits_count', 'reauthorization_notification_days_count']);
        });
    }
}
