<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToPatientAlertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_alerts', function (Blueprint $table) {
            $table->integer('co_pay')->default(0)->after('message');
            $table->integer('deductible')->default(0)->after('co_pay');
            $table->integer('insurance_pay')->default(0)->after('deductible');
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
            $table->dropColumn('co_pay');
            $table->dropColumn('deductible');
            $table->dropColumn('insurance_pay');
        });
    }
}
