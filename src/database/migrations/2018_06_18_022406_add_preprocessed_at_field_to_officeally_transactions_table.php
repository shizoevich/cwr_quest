<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPreprocessedAtFieldToOfficeallyTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('officeally_transactions', function(Blueprint $table) {
            $table->timestamp('preprocessed_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('officeally_transactions', function(Blueprint $table) {
            $table->dropColumn('preprocessed_at');
        });
    }
}
