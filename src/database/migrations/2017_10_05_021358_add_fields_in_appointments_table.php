<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsInAppointmentsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('appointments', function(Blueprint $table) {
            $table->integer('parsed_at')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('appointments', function(Blueprint $table) {
            $table->dropColumn('parsed_at');
            $table->dropColumn('deleted_at');
        });
    }
}
