<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficeallyTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('officeally_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('external_id', 45)->unique();
            $table->integer('patient_id')->index();
            $table->integer('transaction_type_id')->unsigned();
            $table->integer('payment_amount');
            $table->integer('applied_amount');
            $table->timestamp('transaction_date')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('transaction_type_id')
                ->references('id')
                ->on('officeally_transaction_types')
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
        Schema::dropIfExists('officeally_transactions');
    }
}
