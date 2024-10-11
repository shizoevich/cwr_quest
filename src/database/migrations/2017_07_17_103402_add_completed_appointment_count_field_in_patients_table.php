<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCompletedAppointmentCountFieldInPatientsTable extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        if(!Schema::hasColumn('patients', 'completed_appointment_count')) {
            Schema::table('patients', function (Blueprint $table) {
                $table->integer('completed_appointment_count')
                    ->unsigned()
                    ->default(0)
                    ->description('Used for calculate important patients in chart');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        if(Schema::hasColumn('patients', 'completed_appointment_count')) {
            Schema::table('patients', function(Blueprint $table) {
                $table->dropColumn('completed_appointment_count');
            });
        }
    }
}
