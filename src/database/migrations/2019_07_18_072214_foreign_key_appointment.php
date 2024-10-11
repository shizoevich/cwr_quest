<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ForeignKeyAppointment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
        });
        Schema::table('call_logs', function (Blueprint $table) {
            $table->unsignedInteger('appointment_id')->change();

            $table->foreign('appointment_id')->references('id')->on('kaiser_appointments')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->dropForeign(['appointment_id']);
        });

        Schema::table('call_logs', function (Blueprint $table) {
            $table->integer('appointment_id')->change();

            $table->foreign('appointment_id')->references('id')->on('appointments')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }
}
