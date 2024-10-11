<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemovePatientSquareAccountsCreditCardNonceColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_square_accounts', function (Blueprint $table) {
            $table->dropColumn('credit_card_nonce');
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
            $table->string('credit_card_nonce')->nullable()->after('external_id');
        });
    }
}
