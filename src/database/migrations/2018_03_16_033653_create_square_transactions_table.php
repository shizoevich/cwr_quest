<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSquareTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('square_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('external_id', 45)->unique();
            $table->integer('location_id')->unsigned();
            $table->integer('customer_id')->unsigned();
            $table->integer('transaction_type_id')->unsigned();
            $table->integer('amount_money')->unsigned();
            $table->string('card_brand', 45)->nullable();
            $table->integer('card_last_four')->nullable();
            $table->integer('entry_method_id')->nullable()->unsigned();
            $table->timestamp('transaction_date')->nullable();
            $table->timestamps();

            $table->foreign('location_id')
                ->references('id')
                ->on('square_locations')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('transaction_type_id')
                ->references('id')
                ->on('square_transaction_types')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('customer_id')
                ->references('id')
                ->on('patient_square_accounts')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('entry_method_id')
                ->references('id')
                ->on('square_transaction_entry_methods')
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
        Schema::dropIfExists('square_transactions');
    }
}
