<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientNotesTableDeleteUnusedColumns extends Migration {

    private $tableName = 'patient_notes';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if(Schema::hasColumn($this->tableName, 'status_edit_1')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('status_edit_1');
            });
        }
        if(Schema::hasColumn($this->tableName, 'status_edit_2')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('status_edit_2');
            });
        }
        if(Schema::hasColumn($this->tableName, 'status_edit_3')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('status_edit_3');
            });
        }
        if(Schema::hasColumn($this->tableName, 'status_edit_4')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('status_edit_4');
            });
        }
        if(Schema::hasColumn($this->tableName, 'status_edit_5')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('status_edit_5');
            });
        }
        if(Schema::hasColumn($this->tableName, 'facility_name')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('facility_name');
            });
        }
        if(Schema::hasColumn($this->tableName, 'procedure_code')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('procedure_code');
            });
        }
        if(Schema::hasColumn($this->tableName, 'pty_30')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('pty_30');
            });
        }
        if(Schema::hasColumn($this->tableName, 'pty_45')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('pty_45');
            });
        }
        if(Schema::hasColumn($this->tableName, 'pty_60')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('pty_60');
            });
        }
        if(Schema::hasColumn($this->tableName, 'family')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('family');
            });
        }
        if(Schema::hasColumn($this->tableName, 'group')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('group');
            });
        }
        if(Schema::hasColumn($this->tableName, 'units')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('units');
            });
        }
        if(Schema::hasColumn($this->tableName, 'health_behaviour')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('health_behaviour');
            });
        }
        if(Schema::hasColumn($this->tableName, 'session_time')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('session_time');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        //
    }
}
