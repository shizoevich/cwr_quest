<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderWorkHoursTempTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('provider_work_hours_temp', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('provider_id');
            $table->integer('office_id');
            $table->integer('office_room_id');
            $table->tinyInteger('day_of_week');
            $table->time('start_time');
            $table->integer('length');
            $table->timestamp('start_date');
            $table->timestamp('end_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('provider_work_hours_temp');
    }
}
