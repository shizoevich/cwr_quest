<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToPatientVisitDiagnosesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_visit_diagnoses', function(Blueprint $table) {
            $table->foreign('diagnose_id')
                ->references('id')
                ->on('diagnoses')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('visit_id')
                ->references('id')
                ->on('patient_visits')
                ->onDelete('cascade')
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
        Schema::table('patient_visit_diagnoses', function(Blueprint $table) {
            $table->dropForeign(['diagnose_id']);
            $table->dropForeign(['visit_id']);
        });
    }
}
