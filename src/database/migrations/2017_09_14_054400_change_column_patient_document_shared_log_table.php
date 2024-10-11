<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ChangeColumnPatientDocumentSharedLogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_document_shared_logs', function(Blueprint $table) {
            $table->renameColumn('patient_document_statuses_id', 'shared_document_statuses_id');
        });
}

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_document_shared_logs', function(Blueprint $table) {
            $table->renameColumn('shared_document_statuses_id', 'patient_document_statuses_id');
        });
    }
}
