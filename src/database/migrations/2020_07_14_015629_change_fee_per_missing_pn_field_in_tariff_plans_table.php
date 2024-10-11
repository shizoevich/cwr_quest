<?php

use App\TariffPlan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeFeePerMissingPnFieldInTariffPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        TariffPlan::query()->update([
            'fee_per_missing_pn' => \DB::raw('fee_per_missing_pn * 100')
        ]);
        Schema::table('tariffs_plans', function(Blueprint $table) {
            $table->unsignedInteger('fee_per_missing_pn')->default(1500)->change()->comment('Divide by 100 for get original value');   // default $15.00
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
            $table->float('fee_per_missing_pn')->default(15.00)->change();
        });
        TariffPlan::query()->update([
            'fee_per_missing_pn' => \DB::raw('fee_per_missing_pn / 100')
        ]);
    }
}
