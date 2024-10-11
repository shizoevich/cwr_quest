<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransactionPurposeIdColumnToOfficeallyTransactionsTable extends Migration
{
    /** transaction_purpose_id_to_officeally_transactions
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('officeally_transactions', function (Blueprint $table) {
            $table->unsignedInteger('transaction_purpose_id')->nullable()->default(null)->after('transaction_type_id');

            $table->foreign('transaction_purpose_id')
                ->references('id')
                ->on('officeally_transaction_purposes')
                ->onUpdate('SET NULL')
                ->onDelete('SET NULL');
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
            $table->dropForeign(['officeally_transactions_transaction_purpose_id_foreign']);
            $table->dropColumn('transaction_purpose_id');
        });
    }
}
