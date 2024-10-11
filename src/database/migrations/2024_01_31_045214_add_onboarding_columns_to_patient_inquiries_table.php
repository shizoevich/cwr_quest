<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnboardingColumnsToPatientInquiriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_inquiries', function (Blueprint $table) {
            $table->date('onboarding_date')->nullable()->after('is_archived');
            $table->string('onboarding_time')->nullable()->after('onboarding_date');
            $table->string('onboarding_phone')->nullable()->after('onboarding_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_inquiries', function (Blueprint $table) {
            $table->dropColumn('onboarding_date');
            $table->dropColumn('onboarding_time');
            $table->dropColumn('onboarding_phone');
        });
    }
}
