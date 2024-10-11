<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCurrentTrackUpdatedAtFieldToTridiuumPatients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tridiuum_patients', function(Blueprint $table) {
            $table->timestamp('current_track_updated_at')->nullable()->after('current_track');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tridiuum_patients', function(Blueprint $table) {
            $table->dropColumn('current_track_updated_at');
        });
    }
}
