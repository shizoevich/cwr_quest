<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldsToLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('log', function(Blueprint $table) {
            $table->string('type', 8)->after('status_code')->nullable();
            $table->integer('duration')->after('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('log', function(Blueprint $table) {
            $table->dropColumn('duration');
            $table->dropColumn('type');
        });
    }
}
