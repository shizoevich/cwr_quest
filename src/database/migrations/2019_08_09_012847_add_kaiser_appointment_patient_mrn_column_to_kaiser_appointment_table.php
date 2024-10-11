<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKaiserAppointmentPatientMrnColumnToKaiserAppointmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kaiser_appointments', function (Blueprint $table) {
            $table->string('mrn', 64)->nullable()->after('patient_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kaiser_appointments', function (Blueprint $table) {
            $table->dropColumn('mrn');
        });
    }
}