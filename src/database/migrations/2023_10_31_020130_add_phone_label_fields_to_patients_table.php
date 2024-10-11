<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPhoneLabelFieldsToPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('cell_phone_label')->nullable()->after('cell_phone');
            $table->string('home_phone_label')->nullable()->after('home_phone');
            $table->string('work_phone_label')->nullable()->after('work_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn(['cell_phone_label', 'home_phone_label', 'work_phone_label']);
        });
    }
}
