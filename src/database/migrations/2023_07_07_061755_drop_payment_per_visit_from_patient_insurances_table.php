<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropPaymentPerVisitFromPatientInsurancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_insurances', function (Blueprint $table) {
            $table->dropColumn('payment_per_visit');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_insurances', function (Blueprint $table) {
            $table->integer('payment_per_visit')->default(0)->after('zip')->comment('The value that insurance will pay for the visit');
        });
    }
}
