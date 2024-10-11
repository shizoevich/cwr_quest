<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientNoteLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_note_logs', function(Blueprint $table)
        {
            $table->increments('id');
            $table->integer('patient_note_id')->key();
            $table->integer('patient_id')->key();
            $table->integer('provider_id')->key()->nullable();
            $table->integer('user_id')->unsigned()->key();
            $table->tinyInteger('type');
            $table->text('data')->nullable();
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
        Schema::drop('patient_note_logs');
    }
}
