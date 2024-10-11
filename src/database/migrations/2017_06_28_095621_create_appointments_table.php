<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateAppointmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('appointments', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('idAppointments')->nullable()->unique('idAppointments_UNIQUE');
			$table->string('resource', 45)->nullable();
			$table->integer('time')->nullable();
			$table->string('check_in', 45)->nullable();
			$table->string('date_created', 45)->nullable();
			$table->integer('visit_copay')->nullable();
			$table->integer('visit_length')->nullable();
			$table->string('notes')->nullable();
			$table->string('reason_for_visit')->nullable();
			$table->string('sheldued_by', 45)->nullable();
			$table->integer('patients_id')->nullable()->index('fk_Appointments_Patients1_idx');
			$table->integer('providers_id')->nullable()->index('fk_Appointments_Providers1_idx');
			$table->integer('offices_id')->nullable()->index('fk_Appointments_Offices1_idx');
			$table->integer('appointment_statuses_id')->nullable()->index('fk_Appointments_Appointment_statuses1_idx');
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
		Schema::drop('appointments');
	}

}
