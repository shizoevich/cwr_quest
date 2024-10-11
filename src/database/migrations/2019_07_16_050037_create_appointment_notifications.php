<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAppointmentNotifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('appointment_notifications', function(Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id');
            $table->integer('appointment_id');
            $table->integer('type');
            $table->integer('status')->default(1);
            $table->timestamps();

            $table->foreign('provider_id')
                ->references('id')->on('providers')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('appointment_id')
                ->references('id')->on('appointments')
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
        Schema::dropIfExists('appointment_notifications');
    }
}
