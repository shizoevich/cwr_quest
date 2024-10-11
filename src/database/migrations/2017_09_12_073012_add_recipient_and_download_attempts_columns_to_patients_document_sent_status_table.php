<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRecipientAndDownloadAttemptsColumnsToPatientsDocumentSentStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_document_sent_status',
            function (Blueprint $table) {
                $table->string('recipient');
                $table->integer('download_attempts');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_document_sent_status',
            function (Blueprint $table) {
                $table->dropColumn(['recipient', 'download_attempts']);
            });
    }
}
