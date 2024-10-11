<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLateCancellationTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('late_cancellation_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('appointment_id');
            $table->integer('payment_amount');
            $table->timestamp('transaction_date');
            $table->timestamps();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('preprocessed_at')->nullable();

            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
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
        Schema::dropIfExists('late_cancellation_transactions');
    }
}
