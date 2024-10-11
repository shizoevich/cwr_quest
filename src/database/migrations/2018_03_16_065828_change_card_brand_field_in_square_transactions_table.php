<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeCardBrandFieldInSquareTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('square_transactions', function(Blueprint $table) {
            $table->dropColumn('card_brand');
            $table->integer('card_brand_id')->unsigned()->nullable()->after('amount_money');

            $table->foreign('card_brand_id')
                ->references('id')
                ->on('square_card_brands')
                ->onDelete('cascade')
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
            $table->string('card_brand')->nullable()->after('amount_money');
            $table->dropForeign(['card_brand_id']);
            $table->dropColumn('card_brand_id');
        });
    }
}
