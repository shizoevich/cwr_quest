<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPatientNotesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('patient_notes', function(Blueprint $table)
		{
			$table->foreign('patients_id', 'fk_Patient_notes_Patients1')->references('id')->on('patients')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('patient_notes', function(Blueprint $table)
		{
			$table->dropForeign('fk_Patient_notes_Patients1');
		});
	}

}
