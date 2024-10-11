<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFutureInsuranceReauthorizationDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('future_insurance_reauthorization_data', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('form_id')->unsigned();
            $table->string('auth_number', 50)->nullable();
            $table->integer('visits_auth')->nullable();
            $table->date('eff_start_date')->nullable();
            $table->date('eff_stop_date')->nullable();
            $table->timestamps();

            $table->foreign('form_id')
                ->references('id')
                ->on('submitted_reauthorization_request_forms')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('future_insurance_reauthorization_data');
    }
}
