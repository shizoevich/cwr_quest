<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientTwilioSubscribersTable extends Migration
{
    public function up(): void
    {
        Schema::create('patient_twilio_subscribers', static function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id');
            $table->string('phone', 45);
            $table->string('status', 11);
            $table->timestamps();
            $table->foreign('patient_id')->references('id')->on('patients')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_twilio_subscribers');
    }
}
