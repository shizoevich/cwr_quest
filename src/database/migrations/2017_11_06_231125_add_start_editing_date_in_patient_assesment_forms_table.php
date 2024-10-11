<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStartEditingDateInPatientAssesmentFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients_assessment_forms', function(Blueprint $table) {
            $table->timestamp('start_editing_date')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients_assessment_forms', function(Blueprint $table) {
            $table->dropColumn('start_editing_date');
        });
    }
}
