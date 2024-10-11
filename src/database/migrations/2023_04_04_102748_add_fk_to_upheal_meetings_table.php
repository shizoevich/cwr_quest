<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFkToUphealMeetingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('upheal_meetings', function(Blueprint $table) {
            $table->foreign('patient_id')
                ->references('id')
                ->on('patients')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->onDelete('CASCADE')
                ->onUpdate('CASCADE');
            $table->foreign('appointment_id')
                ->references('id')
                ->on('appointments')
                ->onDelete('SET NULL')
                ->onUpdate('CASCADE');
            $table->foreign('notification_id')
                ->references('id')
                ->on('scheduled_notifications')
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
        Schema::table('upheal_meetings', function(Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['provider_id']);
            $table->dropForeign(['appointment_id']);
            $table->dropForeign(['notification_id']);
        });
    }
}
