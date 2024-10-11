<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTridiuumFieldsToOfficesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->string('tridiuum_site_id', 64)->after('office')->nullable();
            $table->boolean('tridiuum_is_enabled')->after('tridiuum_site_id')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('offices', function (Blueprint $table) {
            $table->dropColumn(['tridiuum_site_id']);
            $table->dropColumn(['tridiuum_is_enabled']);
        });
    }
}
