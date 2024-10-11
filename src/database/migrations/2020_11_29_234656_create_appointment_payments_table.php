<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_payments', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('appointment_id');
            $table->unsignedInteger('payment_method_id');
            $table->unsignedInteger('amount')->comment('Amount in cents');
            $table->tinyInteger('is_square_transaction_success');
            $table->tinyInteger('is_officeally_transaction_success');
            $table->text('additional_data')->nullable();
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
        Schema::dropIfExists('appointment_payments');
    }
}
