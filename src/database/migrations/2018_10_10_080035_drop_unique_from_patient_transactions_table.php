<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUniqueFromPatientTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_transactions', function(Blueprint $table) {
            $table->dropUnique('patient_transactions_unique1');
        });
        Schema::table('patient_transactions', function(Blueprint $table) {
            $table->timestamp('detached_at')->nullable()->after('transactionable_type');
            $table->index(['transactionable_id', 'transactionable_type', 'detached_at'], 'transactionable_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
