<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToPatientFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_forms', function (Blueprint $table) {
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers');
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients');
            $table->foreign('reviewed_by')
                ->references('id')
                ->on('users');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_forms', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['reviewed_by']);
        });
    }
}
