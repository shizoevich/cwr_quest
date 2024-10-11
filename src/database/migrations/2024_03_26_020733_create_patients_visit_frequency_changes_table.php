<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePatientsVisitFrequencyChangesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('patients_visit_frequency_changes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('patient_id');
            $table->unsignedTinyInteger('old_visit_frequency_id')->nullable();
            $table->unsignedTinyInteger('new_visit_frequency_id');
            $table->unsignedInteger('changed_by');
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onUpdate('CASCADE')
                ->onDelete('CASCADE');

            $table->foreign('old_visit_frequency_id')
                ->references('id')
                ->on('patient_visit_frequencies')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');

            $table->foreign('new_visit_frequency_id')
                ->references('id')
                ->on('patient_visit_frequencies')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');

            $table->foreign('changed_by')
                ->references('id')
                ->on('users')
                ->onUpdate('NO ACTION')
                ->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('patients_visit_frequency_changes');
    }
}
