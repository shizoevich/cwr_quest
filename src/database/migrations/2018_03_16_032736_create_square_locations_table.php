<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSquareLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('square_locations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('external_id', 45)->unique();
            $table->string('name')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('locality')->nullable();
            $table->string('administrative_district_level_1', 45)->nullable();
            $table->string('postal_code', 45)->nullable();
            $table->string('country', 45)->nullable();
            $table->string('merchant_id')->nullable();
            $table->string('currency', 45)->nullable();
            $table->string('phone_number', 45)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('square_locations');
    }
}
