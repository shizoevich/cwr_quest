<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfficeallyLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('officeally_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_success');
            $table->unsignedTinyInteger('action')->index();
            $table->text('message')->nullable();
            $table->text('data')->nullable();
            
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
        Schema::dropIfExists('officeally_logs');
    }
}
