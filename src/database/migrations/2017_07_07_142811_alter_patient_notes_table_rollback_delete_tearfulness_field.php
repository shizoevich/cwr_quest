<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPatientNotesTableRollbackDeleteTearfulnessField extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasColumn('patient_notes', 'tearfulness')) {
            Schema::table('patient_notes', function (Blueprint $table) {
                $table->boolean('tearfulness')->nullable()->after('disturbed_eating');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_notes', function (Blueprint $table) {
            $table->dropColumn('tearfulness');
        });
    }
}
