<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddExpiringAtToPatientDocumentRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_document_requests', function (Blueprint $table) {
            $table->timestamp('expiring_at')->nullable(true)->after('hash');
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
            $table->dropColumn('expiring_at');
        });
    }
}
