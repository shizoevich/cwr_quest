<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLateCancelationFeeColumnToTherapistSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_survey', function (Blueprint $table) {
            $table->integer('late_cancelation_fee')->nullable()->after('help_description');
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
            $table->dropColumn('late_cancelation_fee');
        });
    }
}
