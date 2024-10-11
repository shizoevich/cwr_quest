<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHexColorInPatientStatusesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('patient_statuses', function(Blueprint $table) {
            $table->string('hex_color', 6)->default('000000');  //default black color
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('patient_statuses', function(Blueprint $table) {
            $table->dropColumn('hex_color');
        });
    }
}
