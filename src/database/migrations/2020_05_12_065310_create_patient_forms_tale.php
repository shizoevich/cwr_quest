<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientFormsTale extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_forms', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('provider_id')->nullable();
            $table->integer('patient_id');
            $table->tinyInteger('type');
            $table->text('data');
            $table->unsignedInteger('reviewed_by')->nullable();
            $table->unsignedTinyInteger('status')->default(0);
            $table->timestamp('reviewed_at')->nullable();
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
        Schema::drop('patient_forms');
    }
}
