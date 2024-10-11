<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrentTrackFieldInTridiuumPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('tridiuum_patients', function (Blueprint $table) {
            $table->string('current_track', 255)->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('tridiuum_patients', function (Blueprint $table) {
            $table->dropColumn('current_track');
        });
    }
}
