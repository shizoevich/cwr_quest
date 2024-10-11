<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnsToPatientLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_leads', function (Blueprint $table) {
            $table->string('auth_number', 50)->nullable()->after('patient_id');
            $table->integer('visits_auth')->nullable()->after('preferred_phone');
            $table->integer('visits_auth_left')->nullable()->after('visits_auth');
            $table->date('eff_start_date')->nullable()->after('visit_copay');
            $table->date('eff_stop_date')->nullable()->after('eff_start_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_leads', function (Blueprint $table) {
            $table->dropColumn(['auth_number', 'visits_auth', 'visits_auth_left', 'eff_start_date', 'eff_stop_date']);
        });
    }
}
