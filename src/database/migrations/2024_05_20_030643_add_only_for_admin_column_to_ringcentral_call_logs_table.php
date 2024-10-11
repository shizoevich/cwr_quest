<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOnlyForAdminColumnToRingcentralCallLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ringcentral_call_logs', function (Blueprint $table) {
            $table->boolean('only_for_admin')->nullable()->default(0)->after('callee_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ringcentral_call_logs', function (Blueprint $table) {
            $table->dropColumn('only_for_admin');
        });
    }
}
