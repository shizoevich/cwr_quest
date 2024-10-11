<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddApproverCommentFieldToPatientRemoveRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('patient_remove_requests', function (Blueprint $table) {
            $table->string('approver_comment')->nullable()
                ->after('approver_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('patient_remove_requests', function (Blueprint $table) {
            $table->dropColumn('approver_comment');
        });
    }
}
