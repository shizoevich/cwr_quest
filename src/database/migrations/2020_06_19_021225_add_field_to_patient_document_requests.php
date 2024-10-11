<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFieldToPatientDocumentRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_document_requests', function (Blueprint $table) {
            $table->unsignedTinyInteger('retrieve_count')->nullable(true)->default(null)->after('sent_to_email');
            $table->timestamp('last_retrieved_at')->nullable(true)->after('retrieve_count');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_document_requests', function (Blueprint $table) {
            $table->dropColumn('retrieve_count');
            $table->dropColumn('last_retrieved_at');
        });
    }
}
