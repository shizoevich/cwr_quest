<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePatientNotesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('patient_notes', function(Blueprint $table)
		{
			$table->integer('id', true);
			$table->string('first_name', 128)->nullable();
            $table->string('last_name', 128)->nullable();
			$table->string('date_of_service', 128)->nullable();
			$table->string('provider_name', 128)->nullable();
			$table->string('provider_license_no', 128)->nullable();
			$table->string('diagnosis_icd_code', 128)->nullable();
			$table->text('long_range_treatment_goal', 65535)->nullable();
			$table->text('shortterm_behavioral_objective', 65535)->nullable();
			$table->boolean('depression')->nullable();
			$table->boolean('withdrawal')->nullable();
			$table->boolean('disturbed_sleep')->nullable();
			$table->boolean('disturbed_eating')->nullable();
			$table->string('treatment_modality', 50)->nullable();

            $table->string('session_time',50)->nullable();
			$table->boolean('hopelessness')->nullable();
			$table->boolean('flat_affect')->nullable();
			$table->boolean('anxiety')->nullable();
			$table->boolean('panic_prone')->nullable();
			$table->boolean('worrisome_thinking')->nullable();
			$table->boolean('phobic_avoidance')->nullable();
            $table->boolean('agitated')->nullable();
            $table->boolean('restless_tension')->nullable();
            $table->boolean('fearfulness')->nullable();
			$table->boolean('verbally_abusive')->nullable();
			$table->boolean('physically_abusive')->nullable();
			$table->boolean('irritable')->nullable();
            $table->boolean('anger_outbursts')->nullable();
			$table->boolean('disruptive_vocalizing')->nullable();
			$table->boolean('interpersonal_conflict')->nullable();
			$table->boolean('emotionally_labile')->nullable();
			$table->boolean('impaired_reality')->nullable();
			$table->boolean('delusions')->nullable();
			$table->boolean('hallucinations_vis')->nullable();
			$table->boolean('hallucinations_aud')->nullable();
			$table->boolean('danger_to_self')->nullable();
			$table->boolean('danger_to_others')->nullable();
			$table->boolean('disordered_thinking')->nullable();
			$table->boolean('disorientation')->nullable();
			$table->string('disorientation_status', 2)->nullable();
			$table->boolean('limited_self_expression')->nullable();
			$table->boolean('limited_memory')->nullable();
			$table->boolean('limited_concentration')->nullable();
			$table->boolean('limited_judgment')->nullable();
			$table->boolean('limited_attention')->nullable();
			$table->boolean('limited_info_processing')->nullable();
			$table->text('additional_comments', 65535)->nullable();
			$table->text('plan', 65535)->nullable();
			$table->text('interventions', 65535)->nullable();
			$table->text('progress_and_outcome', 65535)->nullable();
			$table->string('signature_degree', 45)->nullable();
            $table->string('start_time', 128)->nullable();
            $table->string('end_time', 128)->nullable();
			$table->integer('patients_id')->index('fk_Patient_notes_Patients1_idx');
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
		Schema::drop('patient_notes');
	}

}
