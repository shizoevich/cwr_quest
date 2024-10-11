<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAppointmentIdFkToAppointmentPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointment_payments', function(Blueprint $table) {
            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('payment_method_id')
                ->references('id')
                ->on('square_payment_methods')
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
        Schema::table('appointment_payments', function(Blueprint $table) {
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['payment_method_id']);
        });
    }
}
