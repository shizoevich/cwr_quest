<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTridiuumProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tridiuum_providers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('external_id', 191)->unique()->comment('Tridiuum Provider Id');
            $table->integer('internal_id')->unique()->nullable()->comment('Internal Provider Id');
            $table->string('name');
            $table->string('first_name');
            $table->string('last_name');
            $table->timestamp('custom_reassigned_at')->nullable();
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
        Schema::dropIfExists('tridiuum_providers');
    }
}
