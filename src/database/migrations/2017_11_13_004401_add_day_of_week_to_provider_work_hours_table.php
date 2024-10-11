<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDayOfWeekToProviderWorkHoursTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_work_hours', function(Blueprint $table) {
            $table->tinyInteger('day_of_week')->after('office_room_id');
            $table->dropColumn('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_work_hours', function(Blueprint $table) {
            $table->dropColumn('day_of_week');
            $table->date('date')->after('office_room_id');
        });
    }
}
