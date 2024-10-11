<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFeePerMissingPnToTariffPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tariffs_plans', function(Blueprint $table) {
            $table->float('fee_per_missing_pn')->after('name')->default(15.0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tariffs_plans', function(Blueprint $table) {
            $table->dropColumn('fee_per_missing_pn');
        });
    }
}
