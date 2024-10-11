<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTherapyTypeIdColumnToPatientLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_leads', function (Blueprint $table) {
            $table->tinyInteger('therapy_type_id')->unsigned()->nullable()->after('visit_copay');

            $table->foreign('therapy_type_id')
                ->references('id')
                ->on('patient_therapy_types')
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
        Schema::table('patient_leads', function (Blueprint $table) {
            $table->dropForeign(['therapy_type_id']);

            $table->dropColumn('therapy_type_id');
        });
    }
}
