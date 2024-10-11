<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInitialAssessmentFieldsToAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function(Blueprint $table) {
            $table->string('initial_assessment_type', 64)->nullable()->after('is_initial');
            $table->unsignedInteger('initial_assessment_id')->nullable()->after('initial_assessment_type');
            $table->timestamp('initial_assessment_created_at')->nullable()->after('initial_assessment_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function(Blueprint $table) {
            $table->dropColumn(['initial_assessment_type', 'initial_assessment_id', 'initial_assessment_created_at']);
        });
    }
}
