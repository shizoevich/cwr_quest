<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOriginalPhotoNameColumnToTherapistSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_survey', function (Blueprint $table) {
            $table->string('original_photo_name')->nullable()->after('late_cancelation_fee');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('therapist_survey', function (Blueprint $table) {
            $table->dropColumn('original_photo_name');
        });
    }
}
