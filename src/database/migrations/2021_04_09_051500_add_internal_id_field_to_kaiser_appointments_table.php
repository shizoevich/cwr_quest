<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInternalIdFieldToKaiserAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kaiser_appointments', function(Blueprint $table) {
            $table->integer('internal_id')->nullable()->after('id');
            
            $table->foreign('internal_id')
                ->references('id')
                ->on('appointments')
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
        Schema::table('kaiser_appointments', function(Blueprint $table) {
            $table->dropForeign(['internal_id']);
            $table->dropColumn('internal_id');
        });
    }
}
