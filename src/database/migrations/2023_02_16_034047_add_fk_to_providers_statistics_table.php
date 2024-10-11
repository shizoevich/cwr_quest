<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToProvidersStatisticsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providers_statistics', function (Blueprint $table) {
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('billing_period_id')
                ->references('id')
                ->on('billing_periods')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providers_statistics', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropForeign(['billing_period_id']);
        });
    }
}
