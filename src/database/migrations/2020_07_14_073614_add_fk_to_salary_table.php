<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToSalaryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('salary', function(Blueprint $table) {
            $table->foreign('visit_id')
                ->references('id')
                ->on('patient_visits');
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers');
            $table->foreign('new_record_id')
                ->references('id')
                ->on('salary')
                ->onUpdate('CASCADE')
                ->onDelete('SET NULL');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('salary', function(Blueprint $table) {
            $table->dropForeign(['visit_id']);
            $table->dropForeign(['provider_id']);
            $table->dropForeign(['new_record_id']);
        });
    }
}
