<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsWarningColumnToOfficeallyTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('officeally_transactions', function (Blueprint $table) {
            $table->boolean('is_warning')->after('preprocessed_at')->default(false)->comment('To display the type of error on the front');
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
            $table->dropColumn('is_warning');
        });
    }
}
