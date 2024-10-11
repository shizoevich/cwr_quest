<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTreatmentModalityIdColumnToPatientNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_notes', function (Blueprint $table) {
            $table->integer('treatment_modality_id')->unsigned()->nullable()->after('treatment_modality');

            $table->foreign('treatment_modality_id')
                ->references('id')
                ->on('treatment_modalities')
                ->onDelete('set null')
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
        Schema::table('patient_notes', function (Blueprint $table) {
            $table->dropForeign(['treatment_modality_id']);
            
            $table->dropColumn('treatment_modality_id');
        });
    }
}
