<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSmsErrorToPatientDocumentRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_document_requests', function(Blueprint $table) {
            $table->string('sent_to_phone_error')->nullable()->after('sent_to_phone');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_document_requests', function(Blueprint $table) {
            $table->dropColumn('sent_to_phone_error');
        });
    }
}
