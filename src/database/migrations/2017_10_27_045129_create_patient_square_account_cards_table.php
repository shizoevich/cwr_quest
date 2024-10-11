<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientSquareAccountCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_square_account_cards', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_square_account_id')->unsigned();
            $table->string('card_nonce');
            $table->string('card_id');
            $table->string('card_brand')->nullable();
            $table->string('last_four')->nullable();
            $table->integer('exp_month')->nullable();
            $table->integer('exp_year')->nullable();
            $table->string('cardholder_name')->nullable();

            $table->string('address_line_one')->nullable();
            $table->string('address_line_two')->nullable();
            $table->string('locality')->nullable();
            $table->string('administrative_district_level_one')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_square_account_cards');
    }
}
