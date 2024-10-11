<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPatientsHasProvidersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('patients_has_providers', function(Blueprint $table)
		{
			$table->foreign('patients_id', 'fk_Patients_has_Providers_Patients1')->references('id')->on('patients')->onUpdate('NO ACTION')->onDelete('NO ACTION');
			$table->foreign('providers_id', 'fk_Patients_has_Providers_Providers1')->references('id')->on('providers')->onUpdate('NO ACTION')->onDelete('NO ACTION');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('patients_has_providers', function(Blueprint $table)
		{
			$table->dropForeign('fk_Patients_has_Providers_Patients1');
			$table->dropForeign('fk_Patients_has_Providers_Providers1');
		});
	}

}
