<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFileNextcloudPathInPatientsAssessmentFormsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients_assessment_forms', function (Blueprint $table) {
            $table->string('file_nextcloud_path', 1024);
            $table->integer('s3_file_id');

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
            $table->dropColumn(['file_nextcloud_path', 's3_file_id']);
        });
    }
}
