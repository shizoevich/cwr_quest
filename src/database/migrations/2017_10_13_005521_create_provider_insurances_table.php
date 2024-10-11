<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProviderInsurancesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('provider_insurances', function (Blueprint $table) {
            $table->integer('provider_id')->index();
            $table->integer('insurance_id')->unsigned();

            $table->primary(['provider_id','insurance_id']);

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('insurance_id')
                ->references('id')
                ->on('patient_insurances')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('provider_insurances');
    }
}
