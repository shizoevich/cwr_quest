<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PatientNotesTableAddFields extends Migration {

    private $tableName = 'patient_notes';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if(!Schema::hasColumn($this->tableName, 'treatment_modality')) {
            Schema::table('patient_notes', function (Blueprint $table) {
                $table->string('treatment_modality', 50)->nullable()->after('tearfulness');
            });
        }
        if(!Schema::hasColumn($this->tableName, 'disorientation_status')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->string('disorientation_status', 2)->nullable()->after('disorientation');
            });
        }
        if(!Schema::hasColumn($this->tableName, 'plan')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->text('plan', 65535)->nullable()->after('additional_comments');
            });
        }
        if(!Schema::hasColumn($this->tableName, 'start_time')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->string('start_time', 128)->nullable();
            });
        }
        if(!Schema::hasColumn($this->tableName, 'end_time')) {
            Schema::table($this->tableName, function (Blueprint $table) {
                $table->string('end_time', 128)->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if(Schema::hasColumn($this->tableName, 'treatment_modality')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('treatment_modality');
            });
        }
        if(Schema::hasColumn($this->tableName, 'disorientation_status')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('disorientation_status');
            });
        }
        if(Schema::hasColumn($this->tableName, 'plan')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('plan');
            });
        }
        if(Schema::hasColumn($this->tableName, 'start_time')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('start_time');
            });
        }
        if(Schema::hasColumn($this->tableName, 'end_time')) {
            Schema::table($this->tableName, function(Blueprint $table) {
                $table->dropColumn('end_time');
            });
        }
    }
}
