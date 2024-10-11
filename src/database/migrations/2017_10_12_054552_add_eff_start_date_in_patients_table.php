<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddEffStartDateInPatientsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('patients', function(Blueprint $table) {
            $table->date('eff_start_date')->nullable();
            $table->string('subscriber_id', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('patients', function(Blueprint $table) {
            $table->dropColumn('eff_start_date');
            $table->dropColumn('subscriber_id');
        });
    }
}
