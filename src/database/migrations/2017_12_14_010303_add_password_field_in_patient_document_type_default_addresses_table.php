<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPasswordFieldInPatientDocumentTypeDefaultAddressesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_document_type_default_addresses', function(Blueprint $table) {
            $table->string('password', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_document_type_default_addresses', function(Blueprint $table) {
            $table->dropColumn('password');
        });
    }
}
