<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVideoTrainingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('video_trainings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 191)->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('duration');
            $table->string('source_url');
            $table->string('source_type')->comment('Like \'video/mp4\'');
            $table->unsignedInteger('index');
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
        Schema::dropIfExists('video_trainings');
    }
}
