<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToSquareLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('square_logs', function(Blueprint $table) {
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('SET NULL')
                ->onUpdate('SET NULL');
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('SET NULL')
                ->onUpdate('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('square_logs', function(Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['patient_id']);
        });
    }
}
