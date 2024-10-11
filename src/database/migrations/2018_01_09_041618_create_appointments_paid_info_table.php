<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentsPaidInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointments_paid_info', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('appointment_id');
            $table->char('method',12)->notNull();
            $table->integer('copay')->notNull();
            $table->char('check_no',32)->nullable();
            $table->integer('square_card_id')->unsigned()->nullable();
            $table->char('transaction_id',64)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments');
            $table->foreign('square_card_id')
                ->references('id')
                ->on('patient_square_account_cards');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('appointments_paid_info');
    }
}
