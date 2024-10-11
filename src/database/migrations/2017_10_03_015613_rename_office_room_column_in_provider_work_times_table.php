<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameOfficeRoomColumnInProviderWorkTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_work_hours', function(Blueprint $table) {
            $table->integer('resource')->change();
        });

        Schema::table('provider_work_hours', function(Blueprint $table) {
            $table->renameColumn('resource', 'office_room_id');
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
            $table->renameColumn('office_room_id', 'resource');
        });
    }
}
