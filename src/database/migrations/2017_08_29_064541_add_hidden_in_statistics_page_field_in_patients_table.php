<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHiddenInStatisticsPageFieldInPatientsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('patients', function (Blueprint $table) {
            $table->boolean('hidden_on_patients_without_appointments_statistics')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('hidden_on_patients_without_appointments_statistics');
        });
    }
}
