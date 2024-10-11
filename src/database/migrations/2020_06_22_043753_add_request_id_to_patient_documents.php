<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRequestIdToPatientDocuments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_documents', function (Blueprint $table) {
            $table->unsignedInteger('document_request_id')->nullable(true)->default(null)->after('document_type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_documents', function (Blueprint $table) {
            $table->dropColumn('document_request_id');
        });
    }
}
