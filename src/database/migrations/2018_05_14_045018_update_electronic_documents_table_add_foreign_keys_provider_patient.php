<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateElectronicDocumentsTableAddForeignKeysProviderPatient extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_electronic_documents', function (Blueprint $table) {
            $table->integer('document_type_id')->unsigned()->change();
        });

        Schema::table('patient_electronic_documents', function (Blueprint $table) {
            $table->foreign('provider_id')
                ->references('id')
                ->on('providers');

            $table->foreign('patient_id')
                ->references('id')
                ->on('patients');

            $table->foreign('document_type_id')
                ->references('id')
                ->on('patient_document_types');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_electronic_documents', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropForeign(['patient_id']);
            $table->dropForeign(['document_type_id']);
        });

        Schema::table('patient_electronic_documents', function (Blueprint $table) {
            $table->integer('document_type_id')->change();
        });
    }
}
