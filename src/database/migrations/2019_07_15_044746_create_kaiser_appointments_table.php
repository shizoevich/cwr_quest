<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKaiserAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['tridiuum_id']);
        });

        Schema::create('kaiser_appointments', function(Blueprint $table) {
            $table->increments('id');
            $table->string('tridiuum_id', 128);
            $table->timestamp('start_date');
            $table->unsignedInteger('duration');
            $table->text('notes')->nullable();
            $table->text('reason')->nullable();
            $table->integer('patient_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('sex', 6)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('provider_id');
            $table->integer('status')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')
                ->references('id')->on('patients')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('provider_id')
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
        Schema::table('appointments', function (Blueprint $table) {
            $table->string('tridiuum_id', 128)->nullable()->after('idAppointments');
        });

        Schema::dropIfExists('kaiser_appointments');
    }
}
