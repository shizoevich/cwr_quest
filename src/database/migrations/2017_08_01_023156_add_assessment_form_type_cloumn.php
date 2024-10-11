<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAssessmentFormTypeCloumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients_assessment_forms', function (Blueprint $table) {
            $table->tinyInteger('type');
            $table->tinyInteger('nextcloud_id');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients_assessment_forms', function (Blueprint $table) {
            $table->dropColumn(['type','nextcloud_id']);
        });
    }
}
