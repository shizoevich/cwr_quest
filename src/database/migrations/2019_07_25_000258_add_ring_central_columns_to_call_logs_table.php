<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRingCentralColumnsToCallLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->string('ring_central_session_id', 128)->nullable()->after('ring_central_call_id');
            $table->string('result', 32)->nullable()->after('status');
            $table->string('reason', 128)->nullable()->after('result');
            $table->text('reason_description')->nullable()->after('reason');
            $table->unsignedInteger('duration')->nullable()->after('phone_to');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('call_logs', function (Blueprint $table) {
            $table->dropColumn(['ring_central_session_id']);
            $table->dropColumn(['result']);
            $table->dropColumn(['reason']);
            $table->dropColumn(['reason_description']);
            $table->dropColumn(['duration']);
        });
    }
}
