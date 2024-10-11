<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddStatusFieldInPatientsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('patients', function(Blueprint $table) {
            $table->integer('status_id')->nullable()->unsigned();

            $table->foreign('status_id')
                ->references('id')
                ->on('patient_statuses');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('patients', function(Blueprint $table) {
            $table->dropForeign(['status_id']);
            $table->dropColumn(['status_id']);
        });
    }
}
