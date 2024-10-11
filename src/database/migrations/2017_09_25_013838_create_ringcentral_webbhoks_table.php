<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRingcentralWebbhoksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ringcentral_webhooks', function(Blueprint $table) {
            $table->increments('id');
            $table->string('uri');
            $table->string('webhook_id');
            $table->string('creation_time');
            $table->string('status');
            $table->string('event_filters');
            $table->string('expiration_time');
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
        Schema::dropIfExists('ringcentral_webhooks');
    }
}
