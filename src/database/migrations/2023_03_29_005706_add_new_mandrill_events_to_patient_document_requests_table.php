<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNewMandrillEventsToPatientDocumentRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_document_requests', function (Blueprint $table) {
            $table->timestamp('sent_at')->nullable()->after('mandrill_event_id');
            $table->timestamp('deferral_at')->nullable();
            $table->timestamp('hard_bounced_at')->nullable();
            $table->timestamp('soft_bounced_at')->nullable();
            $table->timestamp('bounced_at')->nullable();
            $table->timestamp('click_at')->nullable();
            $table->timestamp('spam_at')->nullable();
            $table->timestamp('unsub_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
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
            $table->dropColumn('sent_at');
            $table->dropColumn('deferral_at');
            $table->dropColumn('hard_bounced_at');
            $table->dropColumn('soft_bounced_at');
            $table->dropColumn('bounced_at');
            $table->dropColumn('click_at');
            $table->dropColumn('spam_at');
            $table->dropColumn('unsub_at');
            $table->dropColumn('rejected_at');
        });
    }
}

