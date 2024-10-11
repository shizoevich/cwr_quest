<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCallsStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('call_logs', function(Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->integer('patient_id');
            $table->integer('appointment_id');
            $table->string('ring_central_call_id',128);
            $table->string('phone_from',64);
            $table->string('phone_to',64);
            $table->text('comment')->nullable();
            $table->integer('status');
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')->on('users')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('appointment_id')
                ->references('id')->on('appointments')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('patient_id')
                ->references('id')->on('patients')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('call_logs');
    }
}
