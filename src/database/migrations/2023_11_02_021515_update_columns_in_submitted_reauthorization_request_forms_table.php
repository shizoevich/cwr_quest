<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateColumnsInSubmittedReauthorizationRequestFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('submitted_reauthorization_request_forms', function (Blueprint $table) {
            $table->dropIndex('submitted_reauthorization_request_forms_document_id_index');
            $table->unsignedInteger('document_id')->nullable()->change();
            $table->string('document_type')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('submitted_reauthorization_request_forms', function (Blueprint $table) {
            $table->unsignedInteger('document_id')->nullable(false)->index()->change();
            $table->string('document_type')->nullable(false)->change();
        });
    }
}
