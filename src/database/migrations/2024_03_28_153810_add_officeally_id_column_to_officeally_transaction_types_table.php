<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOfficeallyIdColumnToOfficeallyTransactionTypesTable extends Migration
{
    /** transaction_purpose_id_to_officeally_transactions
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('officeally_transaction_types', function (Blueprint $table) {
            $table->unsignedInteger('officeally_id')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('officeally_transactions', function (Blueprint $table) {
            $table->dropColumn('officeally_id');
        });
    }
}
