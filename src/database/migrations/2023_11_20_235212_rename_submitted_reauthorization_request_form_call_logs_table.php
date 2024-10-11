<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class RenameSubmittedReauthorizationRequestFormCallLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('submitted_reauthorization_request_form_call_logs', 'submitted_reauthorization_request_form_logs');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::rename('submitted_reauthorization_request_form_logs', 'submitted_reauthorization_request_form_call_logs');
    }
}
