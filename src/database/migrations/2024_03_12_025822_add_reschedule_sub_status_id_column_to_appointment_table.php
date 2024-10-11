<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRescheduleSubStatusIdColumnToAppointmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedInteger('reschedule_sub_status_id')->nullable()->after('appointment_statuses_id');

            $table->foreign('reschedule_sub_status_id')
                ->references('id')
                ->on('appointment_reschedule_sub_statuses')
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
            $table->dropForeign(['reschedule_sub_status_id']);
            $table->dropColumn('reschedule_sub_status_id');
        });
    }
}
