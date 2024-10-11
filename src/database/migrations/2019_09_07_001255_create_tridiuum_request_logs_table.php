<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTridiuumRequestLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tridiuum_request_logs', function(Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('url', 255);
            $table->string('method', 16);
            $table->text('request_body')->nullable();
            $table->unsignedInteger('status_code')->index();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tridiuum_request_logs');
    }
}
