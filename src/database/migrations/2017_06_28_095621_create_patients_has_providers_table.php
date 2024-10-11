<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientsHasProvidersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('patients_has_providers', function(Blueprint $table)
		{
			$table->integer('patients_id')->index('fk_Patients_has_Providers_Patients1_idx');
			$table->integer('providers_id')->index('fk_Patients_has_Providers_Providers1_idx');
			$table->primary(['patients_id','providers_id']);
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('patients_has_providers');
	}

}
