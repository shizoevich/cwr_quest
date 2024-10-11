<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToAppointmentsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('appointments', function(Blueprint $table)
		{
			$table->foreign('appointment_statuses_id', 'fk_Appointments_Appointment_statuses1')->references('id')->on('appointment_statuses')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('offices_id', 'fk_Appointments_Offices1')->references('id')->on('offices')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('patients_id', 'fk_Appointments_Patients1')->references('id')->on('patients')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('providers_id', 'fk_Appointments_Providers1')->references('id')->on('providers')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('appointments', function(Blueprint $table)
		{
			$table->dropForeign('fk_Appointments_Appointment_statuses1');
			$table->dropForeign('fk_Appointments_Offices1');
			$table->dropForeign('fk_Appointments_Patients1');
			$table->dropForeign('fk_Appointments_Providers1');
		});
	}

}
