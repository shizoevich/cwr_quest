<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('patients', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->integer('patient_id')->nullable()->unique('patient_id_UNIQUE');
			$table->string('patient_account_number', 45)->nullable();
			$table->float('auth_number', 10, 0)->nullable();
			$table->string('first_name', 45)->nullable();
			$table->string('home_phone', 45)->nullable();
			$table->string('last_name', 45)->nullable();
			$table->string('middle_initial', 45)->nullable();
			$table->string('insured_name', 45)->nullable();
			$table->string('secondary_insured_name', 45)->nullable();
			$table->string('address')->nullable();
			$table->string('date_of_birth', 45)->nullable();
			$table->string('age', 45)->nullable();
			$table->string('cell_phone', 45)->nullable();
			$table->string('work_phone', 45)->nullable();
			$table->integer('visits_auth')->nullable();
			$table->integer('visits_auth_left')->nullable();
			$table->string('primary_insurance', 255)->nullable();
			$table->string('secondary_insurance', 255)->nullable();
			$table->string('sex', 6)->nullable();
			$table->string('elig_copay', 45)->nullable();
			$table->string('elig_status', 45)->nullable();
			$table->string('reffering_provider', 45)->nullable();
            $table->integer('completed_appointment_count')
                ->unsigned()
                ->default(0)
                ->description('Used for calculate important patients in chart');
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
		Schema::drop('patients');
	}

}
