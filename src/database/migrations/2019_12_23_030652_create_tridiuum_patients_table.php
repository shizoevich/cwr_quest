<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTridiuumPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tridiuum_patients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('external_id', 128)->unique();
            $table->integer('internal_id')->nullable();
            $table->string('mrn', 16)->nullable();
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('middle_initial', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('parsed_at')->nullable();
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
        Schema::dropIfExists('tridiuum_patients');
    }
}
