<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDateOfServiceTempFieldInPatientNotesTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('patient_notes', function(Blueprint $table) {
            $table->date('date_of_service_temp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('patient_notes', 'date_of_service_temp')) {
            Schema::table('patient_notes', function (Blueprint $table) {
                $table->dropColumn('date_of_service_temp');
            });
        }
    }
}
