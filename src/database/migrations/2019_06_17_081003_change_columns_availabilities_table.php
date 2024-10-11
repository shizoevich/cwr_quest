<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnsAvailabilitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('availabilities', function (Blueprint $table) {
            $table->dropColumn('end_date');
            $table->date('start_date')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('availabilities', static function (Blueprint $table) {
            $table->dropColumn('start_date');
        });

        if (!Schema::hasColumn('availabilities', 'end_date')){
            Schema::table('availabilities', static function (Blueprint $table) {
                $table->timestamp('end_date')->nullable();
            });
        }

        Schema::table('availabilities', static function (Blueprint $table) {
            $table->timestamp('start_date')->nullable()->default(null)->after('length');
        });
    }
}
