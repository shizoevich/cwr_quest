<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientSquareAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_square_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id');
            $table->string('external_id')->nullable();
            $table->string('credit_card_nonce')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_square_accounts', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
        });

        Schema::dropIfExists('patient_square_accounts');
    }
}
