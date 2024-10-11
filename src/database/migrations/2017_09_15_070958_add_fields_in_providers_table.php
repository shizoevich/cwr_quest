<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsInProvidersTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('providers', function(Blueprint $table) {
            $table->integer('officeally_id')->nullable();
            $table->string('license_no')->nullable();
            $table->string('individual_npi')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('providers', function(Blueprint $table) {
            $table->dropColumn('officeally_id');
            $table->dropColumn('license_no');
            $table->dropColumn('individual_npi');
        });
    }
}
