<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInputLabelColumnToAvailabilityTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('availability_types', function (Blueprint $table) {
            $table->string('input_label')->nullable()->after('hex_color')->comment('label for checkbox / radio button');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('availability_types', function (Blueprint $table) {
            $table->dropColumn('input_label');
        });
    }
}
