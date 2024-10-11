<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHexColorAndIndexColumnToAvailabilitySubtypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('availability_subtypes', function (Blueprint $table) {
            $table->string('hex_color', 10)->nullable()->after('type');
            $table->unsignedInteger('index')->default(1)->after('hex_color');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('availability_subtypes', function (Blueprint $table) {
            $table->dropColumn('hex_color');
            $table->dropColumn('index');
        });
    }
}
