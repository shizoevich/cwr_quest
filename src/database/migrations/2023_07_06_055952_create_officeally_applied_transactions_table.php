<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficeallyAppliedTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('officeally_applied_transactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('external_id')->unique();
            $table->integer('applied_transaction_type_id')->unsigned()->key();
            $table->integer('patient_visit_id')->unsigned()->key();
            $table->integer('applied_amount');
            $table->timestamp('applied_date');
            $table->timestamp('transaction_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('officeally_applied_transactions');
    }
}
