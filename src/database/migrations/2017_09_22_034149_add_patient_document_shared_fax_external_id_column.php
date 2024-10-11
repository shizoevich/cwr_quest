<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPatientDocumentSharedFaxExternalIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
     public function up()
     {
         Schema::table('patient_document_shared',
             function (Blueprint $table) {
                 $table->integer('external_id')->nullable()->unsigned()->after('shared_link');
             });
     }
 
     /**
      * Reverse the migrations.
      *
      * @return void
      */
     public function down()
     {
         Schema::table('patient_document_shared',
             function (Blueprint $table) {
                 $table->dropColumn('external_id');
             });
     }
}
