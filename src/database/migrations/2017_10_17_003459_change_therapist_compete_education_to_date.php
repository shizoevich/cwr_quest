<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeTherapistCompeteEducationToDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_survey', function (Blueprint $table) {
            $table->date('complete_education')->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('therapist_survey', static function (Blueprint $table) {
            $table->dropColumn('complete_education');
        });

        Schema::table('therapist_survey', static function (Blueprint $table) {
            $table->timestamp('complete_education')->nullable()->default(null);
        });
    }
}
