<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropUniqueFromPatientPreprocessedTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
       // Schema::table('patient_preprocessed_transactions', function(Blueprint $table) {
        //    $table->dropUnique('transactionable_id_transactionable_type');
       // });
        Schema::table('patient_preprocessed_transactions', function(Blueprint $table) {
            $table->timestamp('detached_at')->nullable()->after('transactionable_type');
            $table->index(['transactionable_id', 'transactionable_type', 'detached_at'], 'preprocessed_transactionable_index');
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
