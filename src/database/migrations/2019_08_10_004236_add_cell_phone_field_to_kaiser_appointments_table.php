<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCellPhoneFieldToKaiserAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kaiser_appointments', function(Blueprint $table) {
            $table->string('cell_phone')->nullable()->after('sex');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kaiser_appointments', function(Blueprint $table) {
            $table->dropColumn('cell_phone');
        });
    }
}
