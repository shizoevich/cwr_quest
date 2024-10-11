<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMandrillRejectedEmailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mandrill_rejected_emails', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email')->unique();
            $table->boolean('is_restored')->default(false);
            $table->integer('rejection_times')->default(0);
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
        Schema::dropIfExists('mandrill_rejected_emails');
    }
}
