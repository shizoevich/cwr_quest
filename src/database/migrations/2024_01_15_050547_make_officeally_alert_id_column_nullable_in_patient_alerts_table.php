<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeOfficeallyAlertIdColumnNullableInPatientAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_alerts', function (Blueprint $table) {
            $table->integer('officeally_alert_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_alerts', function (Blueprint $table) {
            $table->integer('officeally_alert_id')->nullable(false)->change();
        });
    }
}
