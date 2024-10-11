<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPatientSquareAccountsCardForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_square_account_cards', function (Blueprint $table) {
            $table->foreign('patient_square_account_id')
                ->references('id')
                ->on('patient_square_accounts')
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
        Schema::table('patient_square_account_cards', function (Blueprint $table) {
            $table->dropForeign(['patient_square_account_id']);
        });
    }
}
