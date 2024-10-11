<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDeductibleColumnsToPatientLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_leads', function (Blueprint $table) {
            $table->boolean('is_self_pay')->default(false)->after('reffering_provider');
            $table->integer('self_pay')->default(0)->after('is_self_pay');
            $table->integer('deductible')->default(0)->after('visit_copay');
            $table->integer('deductible_met')->default(0)->after('deductible');
            $table->integer('deductible_remaining')->default(0)->after('deductible_met');
            $table->integer('insurance_pay')->default(0)->after('deductible_remaining');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_leads', function (Blueprint $table) {
            $table->dropColumn('is_self_pay');
            $table->dropColumn('self_pay');
            $table->dropColumn('deductible');
            $table->dropColumn('deductible_met');
            $table->dropColumn('deductible_remaining');
            $table->dropColumn('insurance_pay');
        });
    }
}
