<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewTridiuumFieldsToTherapistSurveyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('therapist_survey', function (Blueprint $table) {
            $table->text('bio')->nullable()->after('help_description');
            $table->boolean('is_accept_video_appointments')->default(false)->after('help_description');
            $table->unsignedInteger('group_npi')->nullable()->after('help_description');
            $table->string('tridiuum_external_url')->nullable()->after('help_description');
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
            $table->dropColumn([
                'bio',
                'is_accept_video_appointments',
                'group_npi',
                'tridiuum_external_url',
            ]);
        });
    }
}
