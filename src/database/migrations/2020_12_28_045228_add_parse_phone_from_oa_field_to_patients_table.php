<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddParsePhoneFromOaFieldToPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function(Blueprint $table) {
            $table->boolean('parse_cell_phone')->default(true)->after('cell_phone');
            $table->boolean('parse_work_phone')->default(true)->after('work_phone');
            $table->boolean('parse_home_phone')->default(true)->after('home_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients', function(Blueprint $table) {
            $table->dropColumn(['parse_cell_phone', 'parse_work_phone', 'parse_home_phone']);
        });
    }
}
