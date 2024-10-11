<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToPatientFormTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_form_types', function(Blueprint $table) {
            $table->unsignedSmallInteger('order');
            $table->boolean('visible_in_modal')->default(true);
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
            $table->dropColumn([
                'order',
                'visible_in_modal'
            ]);
        });
    }
}
