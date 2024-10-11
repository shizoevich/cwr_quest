<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patient_leads', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id')->nullable()->default(null);
            $table->string('first_name', 45)->nullable();
            $table->string('last_name', 45)->nullable();
            $table->string('middle_initial', 45)->nullable();
            $table->integer('provider_id')->nullable();
            $table->unsignedInteger('preferred_language_id')->nullable();
            $table->string('email', 255)->nullable();
            $table->string('secondary_email', 255)->nullable();
            $table->string('address')->nullable();
            $table->string('address_2')->nullable();
            $table->string('city', 45)->nullable();
            $table->string('state', 2)->nullable();
            $table->string('zip', 20)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('cell_phone', 45)->nullable();
            $table->string('home_phone', 45)->nullable();
            $table->string('work_phone', 45)->nullable();
            $table->tinyInteger('preferred_phone')->nullable();
            $table->integer('primary_insurance_id')->unsigned()->nullable();
            $table->integer('insurance_plan_id')->unsigned()->nullable();
            $table->unsignedInteger('eligibility_payer_id')->nullable();
            $table->string('sex', 6)->nullable();
            $table->string('reffering_provider', 45)->nullable();
            $table->integer('visit_copay')->nullable();
            $table->string('subscriber_id', 50)->nullable();
            $table->boolean('is_payment_forbidden')->default(false);
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients');
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->onDelete('SET NULL')
                ->onUpdate('CASCADE');
            $table->foreign('primary_insurance_id')
                ->references('id')
                ->on('patient_insurances');
            $table->foreign('insurance_plan_id')
                ->references('id')
                ->on('patient_insurances_plans');
            $table->foreign('eligibility_payer_id')
                ->references('id')
                ->on('eligibility_payers')
                ->onDelete('SET NULL');
            $table->foreign('preferred_language_id')
                ->references('id')
                ->on('languages')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patient_leads');
    }
}
