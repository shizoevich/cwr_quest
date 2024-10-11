<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNeedsDeleteFieldToAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function(Blueprint $table) {
            $table->integer('not_found_count')->default(false)->after('deleted_at')->comment('Количество раз когда парсер не смог спарсить этот аппойтмент');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('appointments', function(Blueprint $table) {
            $table->dropColumn('not_found_count');
        });
    }
}
