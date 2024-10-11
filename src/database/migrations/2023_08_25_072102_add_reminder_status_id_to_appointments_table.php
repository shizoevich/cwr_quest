<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReminderStatusIdToAppointmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->integer('reminder_status_id')->nullable()->unsigned()->key()->after('appointment_statuses_id');

            $table->foreign('reminder_status_id')
                ->references('id')
                ->on('appointment_reminder_statuses')
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
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['reminder_status_id']);
            $table->dropColumn('reminder_status_id');
        });
    }
}
