<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUphealMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('upheal_meetings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id');
            $table->integer('provider_id');
            $table->integer('appointment_id')->nullable();
            $table->unsignedInteger('notification_id')->nullable();
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
        Schema::dropIfExists('upheal_meetings');
    }
}
