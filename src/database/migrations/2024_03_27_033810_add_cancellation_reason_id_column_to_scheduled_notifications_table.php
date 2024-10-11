<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCancellationReasonIdColumnToScheduledNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('scheduled_notifications', function (Blueprint $table) {
            $table->tinyInteger('cancellation_reason_id')->nullable()->after('cancelled_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('scheduled_notifications', function (Blueprint $table) {
            $table->dropColumn('cancellation_reason_id');
        });
    }
}
