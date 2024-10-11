<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLogTypeColumnToSubmittedReauthorizationRequestFormLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submitted_reauthorization_request_form_logs', function (Blueprint $table) {
            $table->tinyInteger('log_type')->after('form_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submitted_reauthorization_request_form_logs', function (Blueprint $table) {
            $table->dropColumn('log_type');
        });
    }
}
