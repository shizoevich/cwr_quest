<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTrainingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trainings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id');
            $table->string('certificate_number', 64)->nullable()->unique('certificate_number');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('score')->unsigned()->default(0);
            $table->timestamps();

            $table->foreign('provider_id', 'FK_trainings_providers')
                ->references('id')->on('providers')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('trainings');
    }
}
