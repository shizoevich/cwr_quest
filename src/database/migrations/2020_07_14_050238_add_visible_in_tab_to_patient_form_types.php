<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVisibleInTabToPatientFormTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_form_types', function(Blueprint $table) {
            $table->boolean('visible_in_tab')->default(1)->after('visible_in_modal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_form_types', function (Blueprint $table) {
            $table->dropColumn('visible_in_tab');
        });
    }
}
