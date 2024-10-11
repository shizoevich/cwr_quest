<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderSupervisorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_supervisors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id')->key();
            $table->integer('supervisor_id')->key();
            $table->datetime('attached_at');
            $table->datetime('detached_at')->nullable();
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
        Schema::dropIfExists('provider_supervisors');
    }
}
