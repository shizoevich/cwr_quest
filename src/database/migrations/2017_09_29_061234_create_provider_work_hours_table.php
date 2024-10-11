<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderWorkHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_work_hours', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id');
            $table->integer('office_id');
            $table->string('resource', 45)->nullable();
            $table->date('date');
            $table->time('start_time');
            $table->integer('length');
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
        Schema::dropIfExists('provider_work_hours');
    }
}
