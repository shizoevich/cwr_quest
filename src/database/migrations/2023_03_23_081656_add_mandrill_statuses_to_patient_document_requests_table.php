<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMandrillStatusesToPatientDocumentRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_document_requests', function (Blueprint $table) {
            $table->string('mandrill_event_id', 1000)->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('opened_at')->nullable();
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
            $table->dropColumn('mandrill_event_id');
            $table->dropColumn('delivered_at');
            $table->dropColumn('opened_at');
        });
    }
}
