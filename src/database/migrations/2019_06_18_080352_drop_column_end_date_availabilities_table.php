<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropColumnEndDateAvailabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('availabilities', 'end_date')){
            Schema::table('availabilities', function (Blueprint $table) {
                $table->dropColumn('end_date');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasColumn('availabilities', 'end_date')){
            Schema::table('availabilities', function (Blueprint $table) {
                $table->timestamp('end_date')->nullable()->default(null);
            });
        }
    }
}
