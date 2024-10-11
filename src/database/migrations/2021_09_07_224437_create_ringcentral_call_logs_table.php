<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRingcentralCallLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ringcentral_call_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->integer('patient_id');
            $table->unsignedInteger('appointment_id');
            $table->string('appointment_type', 64);
            $table->string('ring_central_session_id', 128)->unique();
            $table->string('phone_from', 64)->nullable();
            $table->string('phone_to', 64)->nullable();
            $table->unsignedTinyInteger('telephony_status')->nullable();
            $table->unsignedTinyInteger('call_status')->nullable();
            $table->unsignedTinyInteger('caller_status')->nullable();
            $table->unsignedTinyInteger('callee_status')->nullable();
            $table->text('comment')->nullable();
            $table->timestamp('call_starts_at')->nullable();
            $table->timestamp('call_ends_at')->nullable();
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
        Schema::dropIfExists('ringcentral_call_logs');
    }
}
