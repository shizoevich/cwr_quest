<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNoteDateFieldInPatientNotesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('patient_notes', function(Blueprint $table) {
            $table->timestamp('start_editing_note_date')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('patient_notes', function(Blueprint $table) {
            $table->dropColumn('start_editing_note_date');
            $table->dropColumn('deleted_at');
        });
    }
}
