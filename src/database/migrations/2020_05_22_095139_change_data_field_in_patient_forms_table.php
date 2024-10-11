<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeDataFieldInPatientFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_forms', function(Blueprint $table) {
            \DB::statement("ALTER TABLE `patient_forms` CHANGE COLUMN `data` `data` LONGTEXT NOT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `type`;");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_forms', function(Blueprint $table) {
            \DB::statement("ALTER TABLE `patient_forms` CHANGE COLUMN `data` `data` TEXT NOT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `type`;");
        });
    }
}
