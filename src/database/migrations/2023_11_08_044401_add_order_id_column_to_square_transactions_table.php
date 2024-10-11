<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOrderIdColumnToSquareTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('square_transactions', function (Blueprint $table) {
            $table->integer('order_id')->unsigned()->nullable()->after('entry_method_id');

            $table->foreign('order_id')
                ->references('id')
                ->on('square_orders')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('square_transactions', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
    
            $table->dropColumn('order_id');
        });
    }
}
