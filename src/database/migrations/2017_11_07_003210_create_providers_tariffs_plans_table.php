<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProvidersTariffsPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('providers_tariffs_planes', function (Blueprint $table) {

            $table->increments('id');
            $table->integer('tariff_plane_id')->unsigned();
            $table->integer('provider_id');

            $table->foreign('tariff_plane_id')
                ->references('id')
                ->on('tariffs_planes');

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('providers_tariffs_planes');
    }
}
