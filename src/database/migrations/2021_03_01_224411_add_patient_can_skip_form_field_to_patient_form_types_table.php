<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPatientCanSkipFormFieldToPatientFormTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_form_types', function(Blueprint $table) {
            $table->boolean('patient_can_skip_form')->default(false)->after('is_required');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_form_types', function(Blueprint $table) {
            $table->dropColumn('patient_can_skip_form');
        });
    }
}
