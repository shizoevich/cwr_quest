<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToTridiuumPatientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tridiuum_patients', function (Blueprint $table) {
            $table->foreign('internal_id')
                ->references('id')
                ->on('patients')
                ->onDelete('SET NULL')
                ->onUpdate('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tridiuum_patients', function (Blueprint $table) {
            $table->dropForeign(['internal_id']);
        });
    }
}
